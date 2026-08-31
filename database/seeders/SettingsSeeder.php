<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Models\CashCategory;
class SettingsSeeder extends Seeder {
    public function run(): void {
        $branch = Branch::where('code','MLG')->first();
        foreach([
            ['code'=>'cash','name'=>'Cash','status'=>'active'],
            ['code'=>'transfer','name'=>'Transfer','status'=>'active'],
            ['code'=>'qris','name'=>'QRIS','status'=>'active'],
            ['code'=>'debit','name'=>'Debit','status'=>'active'],
            ['code'=>'credit_card','name'=>'Credit Card','status'=>'active'],
            ['code'=>'e_wallet','name'=>'E-Wallet','status'=>'active'],
        ] as $pm) PaymentMethod::firstOrCreate(['code'=>$pm['code']], $pm);
        foreach([
            ['name'=>'Modal Awal','type'=>'income'],['name'=>'Penjualan','type'=>'income'],['name'=>'Pembayaran Hutang','type'=>'income'],['name'=>'Pendapatan Lain','type'=>'income'],
            ['name'=>'Detergent','type'=>'expense'],['name'=>'Listrik','type'=>'expense'],['name'=>'Gaji','type'=>'expense'],['name'=>'Operasional','type'=>'expense'],['name'=>'Transport','type'=>'expense'],
        ] as $cc) CashCategory::firstOrCreate(['name'=>$cc['name'],'type'=>$cc['type']], ['status'=>'active']);
        $mid = \App\Models\Merchant::first()?->id;
        foreach(\App\Models\LaundryItemType::defaults() as $d) \App\Models\LaundryItemType::firstOrCreate(['code'=>$d['code'],'merchant_id'=>$mid], $d+['merchant_id'=>$mid]);
        $settings = [
            ['key'=>'app_name','value'=>'Londry POS','type'=>'string'],
            ['key'=>'company_name','value'=>'Londry Laundry','type'=>'string'],
            ['key'=>'currency','value'=>'IDR','type'=>'string'],
            ['key'=>'timezone','value'=>'Asia/Jakarta','type'=>'string'],
            ['key'=>'receipt_header','value'=>'Terima kasih telah menggunakan layanan kami','type'=>'string'],
            ['key'=>'receipt_footer','value'=>'Barang yang sudah dicuci tidak dapat dikembalikan','type'=>'string'],
            ['key'=>'receipt_size','value'=>'80mm','type'=>'string'],
            ['key'=>'allow_discount','value'=>'1','type'=>'boolean'],
            ['key'=>'allow_partial_payment','value'=>'1','type'=>'boolean'],
            ['key'=>'whatsapp_enabled','value'=>'0','type'=>'boolean'],
        ];
        foreach($settings as $s) Setting::firstOrCreate(['branch_id'=>null,'merchant_id'=>$mid,'key'=>$s['key']], $s+['merchant_id'=>$mid]);
        if($branch) Setting::firstOrCreate(['branch_id'=>$branch->id,'key'=>'branch_receipt_header'], ['value'=>'Londry Malang - Soekarno Hatta','type'=>'string','merchant_id'=>$mid]);
    }
}
