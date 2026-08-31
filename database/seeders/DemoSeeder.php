<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Customer;
use App\Models\ProductStock;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
class DemoSeeder extends Seeder {
    /**
     * Hanya data dummy/DEMO, terisolasi is_demo=true.
     * Jalankan setelah ProductionSeeder: php artisan db:seed --class=DemoSeeder
     * Idempotent (firstOrCreate).
     */
    public function run(): void {
        $merchant = Merchant::first();
        $demoBranch = Branch::firstOrCreate(['code'=>'DEMO'], [
            'name'=>'Cabang DEMO','phone'=>'0800-DEMO','email'=>'demo@londry.test',
            'address'=>'Jl. Demo No. 1 (data dummy)','city'=>'Demo','province'=>'Demo','postal_code'=>'00000','status'=>'active',
            'merchant_id'=>$merchant?->id,'is_demo'=>true,
        ]);
        if(!$demoBranch->is_demo) $demoBranch->update(['is_demo'=>true]);
        if($demoBranch->merchant_id===null && $merchant) $demoBranch->update(['merchant_id'=>$merchant->id]);

        $adminRole = Role::where('name','Admin')->first();
        $kasirRole = Role::where('name','Kasir')->first();

        $demoAdmin = User::firstOrCreate(['email'=>'demo.admin@londry.test'], [
            'name'=>'Demo Admin','username'=>'demo_admin','phone'=>'080000000001','status'=>'active',
            'branch_id'=>$demoBranch->id,'merchant_id'=>$merchant?->id,'password'=>Hash::make('demo123'),'is_demo'=>true,
        ]);
        if(!$demoAdmin->is_demo) $demoAdmin->update(['is_demo'=>true, 'merchant_id'=>$merchant?->id, 'branch_id'=>$demoBranch->id]);
        $demoAdmin->roles()->syncWithoutDetaching([$adminRole->id]);
        $demoAdmin->branches()->syncWithoutDetaching([$demoBranch->id]);

        $demoKasir = User::firstOrCreate(['email'=>'demo.kasir@londry.test'], [
            'name'=>'Demo Kasir','username'=>'demo_kasir','phone'=>'080000000002','status'=>'active',
            'branch_id'=>$demoBranch->id,'merchant_id'=>$merchant?->id,'password'=>Hash::make('demo123'),'is_demo'=>true,
        ]);
        if(!$demoKasir->is_demo) $demoKasir->update(['is_demo'=>true, 'merchant_id'=>$merchant?->id, 'branch_id'=>$demoBranch->id]);
        $demoKasir->roles()->syncWithoutDetaching([$kasirRole->id]);
        $demoKasir->branches()->syncWithoutDetaching([$demoBranch->id]);

        // stock demo branch
        foreach(\App\Models\Product::where('type','product')->get() as $prod){
            ProductStock::firstOrCreate(['product_id'=>$prod->id,'branch_id'=>$demoBranch->id], ['quantity'=>100,'minimum_stock'=>10]);
        }

        Customer::firstOrCreate(['code'=>'DEMO-000000'], ['name'=>'Walk-in DEMO','phone'=>'000000','email'=>null,'address'=>'-','notes'=>'Walk-in demo','status'=>'active','branch_id'=>$demoBranch->id,'merchant_id'=>$merchant?->id,'is_demo'=>true]);
        Customer::firstOrCreate(['code'=>'DEMO-000001'], ['name'=>'Demo Customer 1','phone'=>'081900000001','email'=>'demo1@test.com','address'=>'Jl. Demo 1','status'=>'active','branch_id'=>$demoBranch->id,'merchant_id'=>$merchant?->id,'is_demo'=>true]);
        Customer::firstOrCreate(['code'=>'DEMO-000002'], ['name'=>'Demo Customer 2','phone'=>'081900000002','email'=>'demo2@test.com','address'=>'Jl. Demo 2','status'=>'active','branch_id'=>$demoBranch->id,'merchant_id'=>$merchant?->id,'is_demo'=>true]);
        Customer::firstOrCreate(['code'=>'DEMO-000003'], ['name'=>'Demo Customer 3','phone'=>'081900000003','email'=>'demo3@test.com','address'=>'Jl. Demo 3','status'=>'active','branch_id'=>$demoBranch->id,'merchant_id'=>$merchant?->id,'is_demo'=>true]);
    }
}
