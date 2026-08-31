<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Services\WhatsappService;
use App\Services\OrderService;
use Illuminate\Http\Request;
class OrderController extends Controller {
    private function assertOrderAccess(Order $order): void {
        $isDemo=(bool)auth()->user()->is_demo;
        if((bool)$order->is_demo !== $isDemo) abort(403, 'Tidak punya akses order ini');
        $mid = auth()->user()->merchant_id;
        if($mid && (int)$order->merchant_id !== (int)$mid) abort(403,'Bukan order merchant Anda');
    }
    public function index(Request $r){
        $branchId = session('branch_id');
        $isDemo=(bool)auth()->user()->is_demo;
        $mid = auth()->user()->merchant_id;
        $q = Order::with(['customer','cashier','branch'])->when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('is_demo',$isDemo)->latest();
        if($s=$r->search) $q->where(fn($qq)=>$qq->where('order_number','like',"%$s%")->orWhereHas('customer', fn($qq)=>$qq->where('name','like',"%$s%")));
        if($r->status) $q->where('order_status',$r->status);
        if($r->payment_status) $q->where('payment_status',$r->payment_status);
        if($r->from) $q->whereDate('order_date','>=',$r->from);
        if($r->to) $q->whereDate('order_date','<=',$r->to);
        $orders = $q->paginate(15);
        return view('orders.index', compact('orders'));
    }
    public function show(Order $order){
        $this->assertOrderAccess($order);
        $order->load(['items.product','payments','customer','branch','cashier']);
        return view('orders.show', compact('order'));
    }
    public function receipt(Order $order){
        $this->assertOrderAccess($order);
        $order->load(['items','customer','branch','cashier','payments']);
        return view('orders.receipt', compact('order'));
    }
    public function updateStatus(Request $r, Order $order, OrderService $svc){
        $this->assertOrderAccess($order);
        $r->validate(['order_status'=>'required|in:received,washing,drying,ironing,ready,picked_up,cancelled']);
        $svc->updateStatus($order, $r->order_status, auth()->user());
        return back()->with('success','Status diupdate ke '.$r->order_status);
    }
    public function cancel(Request $r, Order $order, OrderService $svc){
        $this->assertOrderAccess($order);
        $r->validate(['cancel_reason'=>'required|string']);
        $svc->cancel($order, $r->cancel_reason, auth()->user());
        return back()->with('success','Order dicancel');
    }
    public function addPayment(Request $r, Order $order, OrderService $svc){
        $this->assertOrderAccess($order);
        $r->validate(['amount'=>'required|numeric|min:1','payment_method'=>'required']);
        $svc->addPayment($order, $r->only(['amount','payment_method','reference_number','notes']), auth()->user());
        return back()->with('success','Pembayaran ditambahkan');
    }
    public function updateCustomer(Request $r, Order $order){
        $this->assertOrderAccess($order);
        if(in_array($order->order_status, ['picked_up','cancelled'])){
            return back()->with('error','Order sudah '.$order->order_status.' tidak bisa ganti customer');
        }
        $r->validate(['customer_id'=>'required|exists:customers,id']);
        $customer = \App\Models\Customer::findOrFail($r->customer_id);
        $isDemo = (bool)auth()->user()->is_demo;
        if((bool)$customer->is_demo !== $isDemo) abort(403,'Customer bukan milik demo/production Anda');
        $mid = auth()->user()->merchant_id;
        if($mid && (int)$customer->merchant_id !== (int)$mid) abort(403,'Customer bukan merchant Anda');
        $old = $order->customer_id;
        $order->update(['customer_id'=>$customer->id]);
        try{ \App\Support\AuditLogger::log('update_customer','orders','Order',$order->id, ['customer_id'=>$old], ['customer_id'=>$customer->id]); }catch(\Throwable $e){}
        return back()->with('success','Customer diganti ke '.$customer->name);
    }
    public function sendWhatsapp(Request $r, Order $order, WhatsappService $wa){
        $this->assertOrderAccess($order);
        $order->load(['items','customer','branch','cashier']);
        if(!$order->customer || !$order->customer->phone || $order->customer->phone==='000000'){
            if($r->wantsJson()) return response()->json(['ok'=>false,'message'=>'No HP customer tidak tersedia'],422);
            return back()->with('error','No HP customer tidak tersedia');
        }
        $res = $wa->send($order);
        if($r->wantsJson()) return response()->json($res);
        // direk: langsung buka web.whatsapp.com (sesuai example code user), wa.me sebagai fallback
        if(isset($res['link'])){
            // simpan wa.me juga jika ada untuk copy alternatif
            $msg = $res['message'] ?? 'Membuka WhatsApp...';
            if(!$res['ok']) $msg = ($res['message'] ?? 'Gagal API, membuka link WA').' | WA: '.$res['link'];
            return redirect()->away($res['link'])->with('success', $msg.' | Alternatif wa.me: '.($res['wa_me_link'] ?? $res['link']));
        }
        if($res['ok']) return back()->with('success','Struk berhasil dikirim via WA');
        return back()->with('error', $res['message'] ?? 'Gagal kirim WA');
    }
    public function print(Order $order){
        $this->assertOrderAccess($order);
        $order->load(['items','customer','branch','cashier','payments']);
        return view('orders.print', compact('order'));
    }
}
