<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class MerchantSeeder extends Seeder {
    public function run(): void {
        // Merchant default untuk data existing
        $owner = User::where('username','admin')->first();
        $merchant = Merchant::firstOrCreate(['code'=>'LONDRY-001'], [
            'name'=>'Londry Pusat','slug'=>Str::slug('Londry Pusat').'-001',
            'phone'=>'0341-123456','address'=>'Jl. Soekarno Hatta No. 9','city'=>'Malang','status'=>'active',
            'owner_user_id'=>$owner?->id,
        ]);
        // backfill branch/user/customer/order merchant_id yang masih null
        foreach(Branch::whereNull('merchant_id')->get() as $b) $b->update(['merchant_id'=>$merchant->id]);
        foreach(User::whereNull('merchant_id')->get() as $u) $u->update(['merchant_id'=>$merchant->id]);
        foreach(\App\Models\Customer::whereNull('merchant_id')->get() as $c){
            $br = $c->branch; $c->update(['merchant_id'=>$br?->merchant_id ?? $merchant->id]);
        }
        foreach(\App\Models\Order::whereNull('merchant_id')->get() as $o){
            $br = $o->branch; $o->update(['merchant_id'=>$br?->merchant_id ?? $merchant->id]);
        }
    }
}
