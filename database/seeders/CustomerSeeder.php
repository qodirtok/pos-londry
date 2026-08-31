<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Branch;
class CustomerSeeder extends Seeder {
    public function run(): void {
        $branch = Branch::where('code','MLG')->first() ?? Branch::first();
        $merchantId = $branch?->merchant_id;
        Customer::firstOrCreate(['code'=>'CUST-000000'], ['name'=>'Walk-in Customer','phone'=>'000000','email'=>null,'address'=>'-','notes'=>'Customer umum','status'=>'active','branch_id'=>$branch->id,'merchant_id'=>$merchantId,'is_demo'=>false]);
        Customer::firstOrCreate(['code'=>'CUST-000001'], ['name'=>'Budi Santoso','phone'=>'081234000001','email'=>'budi@test.com','address'=>'Jl. Mawar 1 Malang','status'=>'active','branch_id'=>$branch->id,'merchant_id'=>$merchantId,'is_demo'=>false]);
        Customer::firstOrCreate(['code'=>'CUST-000002'], ['name'=>'Siti Aminah','phone'=>'081234000002','email'=>'siti@test.com','address'=>'Jl. Melati 2 Malang','status'=>'active','branch_id'=>$branch->id,'merchant_id'=>$merchantId,'is_demo'=>false]);
    }
}
