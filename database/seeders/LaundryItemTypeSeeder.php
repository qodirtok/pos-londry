<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\LaundryItemType;
class LaundryItemTypeSeeder extends Seeder {
    public function run(): void {
        foreach(LaundryItemType::defaults() as $d){
            LaundryItemType::firstOrCreate(['code'=>$d['code']], $d);
        }
    }
}
