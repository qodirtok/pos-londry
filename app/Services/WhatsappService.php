<?php
namespace App\Services;
use App\Models\Order;
use App\Models\WhatsappLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
class WhatsappService {
    public function buildMessage(Order $order): string {
        $order->loadMissing(['branch','customer','items','cashier','branch']);
        $custName = $order->customer->name ?? 'Kak';
        $company = Setting::get('company_name','Londry Laundry');
        $branchName = $order->branch->name ?? '';
        $msg = "Halo {$custName} \xF0\x9F\x91\x8B\n\n";
        $msg .= "*{$company}*";
        if($branchName) $msg .= " - {$branchName}";
        $msg .= "\n";
        $branchAddr = trim((string)($order->branch->address ?? ''));
        if($branchAddr) $msg .= "{$branchAddr}\n";
        $branchPhone = trim((string)($order->branch->phone ?? ''));
        if($branchPhone) $msg .= "{$branchPhone}\n";
        $msg .= "Terima kasih telah menggunakan layanan kami.\n\n";
        $msg .= "\xF0\x9F\xA7\xBE *STRUK LAUNDRY*\n";
        $msg .= "No. Transaksi: {$order->order_number}\n";
        $tgl = $order->order_date ? $order->order_date->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
        $msg .= "Tanggal: {$tgl}\n";
        $kasir = $order->cashier->name ?? '-';
        $msg .= "Kasir: {$kasir}\n";
        $custPhone = $order->customer->phone ?? '';
        if($custPhone && $custPhone !== '000000') $msg .= "Customer: {$custName} ({$custPhone})\n";
        // status + rincian laundry (dinamis)
        $statusLabel = ucfirst(str_replace('_',' ', $order->order_status));
        $msg .= "Status: {$statusLabel}\n\n";
        if(!empty($order->laundry_details)){
            $d=$order->laundry_details;
            $map=[];
            try { $codes=array_keys(array_filter($d, fn($k)=>!in_array($k,['catatan','lainnya_desc']), ARRAY_FILTER_USE_KEY)); $map=\App\Models\LaundryItemType::whereIn('code',$codes)->pluck('name','code')->toArray(); } catch(\Throwable $e){}
            $rincian=[];
            foreach($d as $k=>$v){
                if(in_array($k,['catatan','lainnya_desc'])) continue;
                if($v && $v!=='0' && $v!==''){
                    $label=$map[$k] ?? ucfirst(str_replace('_',' ',$k));
                    $rincian[]="{$label}: {$v} pcs";
                }
            }
            if(!empty($rincian) || !empty($d['lainnya_desc']) || !empty($d['catatan'])){
                $msg .= "Rincian Laundry:\n";
                foreach($rincian as $r) $msg .= "- {$r}\n";
                if(!empty($d['lainnya_desc'])) $msg .= "Ket. Lainnya: {$d['lainnya_desc']}\n";
                if(!empty($d['catatan'])) $msg .= "Catatan: {$d['catatan']}\n";
                $msg .= "\n";
            }
        }
        // items
        foreach($order->items as $it){
            $qty = rtrim(rtrim(number_format((float)$it->quantity,3,',','.'),'0'), ',');
            $price = number_format((float)$it->price,0,',','.');
            $sub = number_format((float)$it->subtotal,0,',','.');
            $msg .= "{$it->product_name}\n";
            $msg .= "{$qty} {$it->unit} x Rp{$price}\n";
            $msg .= "Subtotal: Rp{$sub}\n\n";
        }
        $msg .= "--------------------------\n";
        $msg .= "Subtotal: Rp".number_format((float)$order->subtotal,0,',','.')."\n";
        if((float)$order->discount > 0) $msg .= "Diskon: Rp".number_format((float)$order->discount,0,',','.')."\n";
        if((float)$order->tax > 0) $msg .= "Pajak: Rp".number_format((float)$order->tax,0,',','.')."\n";
        $msg .= "*TOTAL: Rp".number_format((float)$order->total,0,',','.')."*\n";
        $msg .= "Bayar: Rp".number_format((float)$order->paid_amount,0,',','.')."\n";
        $kembalian = (float)($order->change_amount ?? 0);
        $msg .= "Kembalian: Rp".number_format($kembalian,0,',','.')."\n";
        $msg .= "--------------------------\n\n";
        $payStatus = $order->payment_status ?? '-';
        $msg .= "Pembayaran: {$payStatus} | Status Laundry: {$statusLabel}\n";
        if(!empty($order->pickup_date)){
            $pickup = $order->pickup_date instanceof \Carbon\Carbon ? $order->pickup_date->format('d/m/Y') : (string)$order->pickup_date;
            $msg .= "Estimasi selesai: {$pickup}\n";
        }
        $msg .= "\nTerima kasih \xF0\x9F\x99\x8F\n";
        $msg .= $company;
        if($branchPhone) $msg .= " - {$branchPhone}";
        return $msg;
    }

    public function send(Order $order): array {
        $phone = $order->customer->phone ?? null;
        if(!$phone || $phone==='000000'){
            return ['ok'=>false, 'message'=>'No HP customer tidak tersedia'];
        }
        $msg = $this->buildMessage($order);
        $phoneNorm = preg_replace('/[^0-9]/','',$phone);
        if(str_starts_with($phoneNorm,'0')) $phoneNorm = '62'.substr($phoneNorm,1);

        $log = WhatsappLog::create([
            'order_id'=>$order->id,
            'phone'=>$phoneNorm,
            'message'=>$msg,
            'status'=>'pending',
        ]);

        $enabled = Setting::get('whatsapp_enabled','0') === '1' || Setting::get('whatsapp_enabled','0') == 1;
        $apiUrl = Setting::get('whatsapp_api_url');
        $apiKey = Setting::get('whatsapp_api_key');

        // Try real API if configured
        if($enabled && $apiUrl){
            try {
                $res = Http::timeout(8)->withHeaders($apiKey ? ['Authorization'=>'Bearer '.$apiKey] : [])->post($apiUrl, [
                    'phone'=>$phoneNorm,
                    'message'=>$msg,
                ]);
                if($res->successful()){
                    $log->update(['status'=>'sent','response'=>$res->body()]);
                    return ['ok'=>true,'via'=>'api','log'=>$log];
                } else {
                    $log->update(['status'=>'failed','response'=>$res->body()]);
                    // fallback to wa.me link
                }
            } catch(\Throwable $e){
                $log->update(['status'=>'failed','response'=>$e->getMessage()]);
            }
        }

        $waLink = 'https://wa.me/' . $phoneNorm . '?text=' . urlencode($msg);

        $log->update([
            'status' => 'sent',
            'response' => 'wa.me direct: ' . $waLink
        ]);
        
        return [
            'ok' => true,
            'via' => 'wa_me',
            'link' => $waLink,
            'log' => $log,
            'message' => 'Link WhatsApp siap — langsung buka wa.me'
        ];
        // return ['ok'=>false,'via'=>'api','link'=>$waLink,'wa_me_link'=>$waMeLink,'log'=>$log,'message'=>'Gagal kirim via API, gunakan link WA'];
    }
}
