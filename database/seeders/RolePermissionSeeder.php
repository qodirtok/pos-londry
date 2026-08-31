<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
class RolePermissionSeeder extends Seeder {
    public function run(): void {
        $perms = [
            'dashboard.view','users.view','users.create','users.update','users.delete',
            'branches.view','branches.create','branches.update','branches.delete',
            'merchants.view','merchants.create','merchants.update','merchants.delete',
            'customers.view','customers.create','customers.update','customers.delete',
            'categories.view','categories.create','categories.update','categories.delete',
            'products.view','products.create','products.update','products.delete',
            'pos.access','orders.view','orders.create','orders.update','orders.cancel',
            'payments.create','payments.refund','cash.view','cash.create','cash.update',
            'reports.view','settings.view','settings.update',
        ];
        foreach($perms as $p) Permission::firstOrCreate(['name'=>$p], ['display_name'=>ucwords(str_replace(['.','_'],' ',$p)), 'group'=>explode('.',$p)[0]]);
        $adminRole = Role::firstOrCreate(['name'=>'Admin'], ['display_name'=>'Admin','description'=>'Full access']);
        $kasirRole = Role::firstOrCreate(['name'=>'Kasir'], ['display_name'=>'Kasir','description'=>'POS & transaction access']);
        $userRole  = Role::firstOrCreate(['name'=>'User'], ['display_name'=>'User','description'=>'Limited access']);
        $managerRole = Role::firstOrCreate(['name'=>'Manager'], ['display_name'=>'Manager','description'=>'Sub-admin merchant (bisa kelola kasir & produk)']);
        $allPerms = Permission::all();
        $adminRole->permissions()->syncWithoutDetaching($allPerms->pluck('id'));
        $kasirPerms = Permission::whereIn('name', ['dashboard.view','customers.view','customers.create','customers.update','pos.access','orders.view','orders.create','orders.update','orders.cancel','payments.create','cash.view','cash.create','cash.update','reports.view'])->get();
        $kasirRole->permissions()->syncWithoutDetaching($kasirPerms->pluck('id'));
        $userRole->permissions()->syncWithoutDetaching(Permission::whereIn('name',['dashboard.view','orders.view'])->pluck('id'));
        $managerPerms = Permission::whereIn('name',['dashboard.view','users.view','users.create','users.update','customers.view','customers.create','customers.update','customers.delete','categories.view','categories.create','categories.update','products.view','products.create','products.update','pos.access','orders.view','orders.create','orders.update','payments.create','cash.view','cash.create','reports.view','settings.view'])->get();
        $managerRole->permissions()->syncWithoutDetaching($managerPerms->pluck('id'));
    }
}
