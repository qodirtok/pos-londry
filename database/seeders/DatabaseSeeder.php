<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Dev / full seed: production + demo (idempotent).
     * - Production only: php artisan db:seed --class=ProductionSeeder
     * - Demo only: php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call([
            ProductionSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
