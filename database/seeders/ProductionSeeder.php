<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class ProductionSeeder extends Seeder {
    /**
     * Seed untuk home server / production: tanpa DEMO, tanpa dummy.
     * Jalankan: php artisan db:seed --class=ProductionSeeder
     */
    public function run(): void {
        $this->call([
            MerchantSeeder::class,
            BranchSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            CatalogSeeder::class,
            CustomerSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
