<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use App\Models\Merchant;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder {
    public function run(): void {
        $branch = Branch::where('code','MLG')->first() ?? Branch::first();
        $branch2 = Branch::where('code','SBY')->first();
        $merchant = Merchant::first();
        $adminRole = Role::where('name','Admin')->first();
        $kasirRole = Role::where('name','Kasir')->first();

        $admin = User::firstOrCreate(['email'=>'admin@londry.test'], [
            'name'=>'Admin Londry','username'=>'admin','phone'=>'081234567890','status'=>'active',
            'branch_id'=>$branch?->id,'merchant_id'=>$merchant?->id,'password'=>Hash::make('password'),'is_demo'=>false,
        ]);
        if($admin->merchant_id===null) $admin->update(['merchant_id'=>$merchant?->id]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        $admin->branches()->syncWithoutDetaching(array_filter([$branch?->id, $branch2?->id]));

        $kasir = User::firstOrCreate(['email'=>'kasir@londry.test'], [
            'name'=>'Kasir Malang','username'=>'kasir','phone'=>'081234567891','status'=>'active',
            'branch_id'=>$branch?->id,'merchant_id'=>$merchant?->id,'password'=>Hash::make('password'),'is_demo'=>false,
        ]);
        if($kasir->merchant_id===null) $kasir->update(['merchant_id'=>$merchant?->id]);
        $kasir->roles()->syncWithoutDetaching([$kasirRole->id]);
        $kasir->branches()->syncWithoutDetaching(array_filter([$branch?->id]));
    }
}
