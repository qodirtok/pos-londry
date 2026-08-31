<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Merchant;
class BranchSeeder extends Seeder {
    public function run(): void {
        $defaultMerchant = Merchant::first();
        $branch = Branch::firstOrCreate(['code'=>'MLG'], [
            'name'=>'Cabang Malang Pusat','phone'=>'0341-123456','email'=>'malang@londry.test',
            'address'=>'Jl. Soekarno Hatta No. 9','city'=>'Malang','province'=>'Jawa Timur','postal_code'=>'65141','status'=>'active',
            'merchant_id'=>$defaultMerchant?->id, 'is_demo'=>false,
        ]);
        if($branch->merchant_id===null && $defaultMerchant) $branch->update(['merchant_id'=>$defaultMerchant->id]);
        $branch2 = Branch::firstOrCreate(['code'=>'SBY'], [
            'name'=>'Cabang Surabaya','phone'=>'031-987654','email'=>'surabaya@londry.test',
            'address'=>'Jl. Pemuda No. 10','city'=>'Surabaya','province'=>'Jawa Timur','postal_code'=>'60271','status'=>'active',
            'merchant_id'=>$defaultMerchant?->id, 'is_demo'=>false,
        ]);
        if($branch2->merchant_id===null && $defaultMerchant) $branch2->update(['merchant_id'=>$defaultMerchant->id]);
    }
}
