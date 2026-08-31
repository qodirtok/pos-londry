<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LaundryItemType extends Model {
    protected $fillable = ['code','name','icon','branch_id','merchant_id','status','sort_order'];
    protected $casts = ['sort_order'=>'integer'];
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function scopeActive($q){ return $q->where('status','active'); }
    public static function defaults(): array {
        return [
            ['code'=>'baju','name'=>'Baju','icon'=>'👕','sort_order'=>1],
            ['code'=>'celana','name'=>'Celana','icon'=>'👖','sort_order'=>2],
            ['code'=>'jaket','name'=>'Jaket','icon'=>'🧥','sort_order'=>3],
            ['code'=>'sepatu','name'=>'Sepatu','icon'=>'👟','sort_order'=>4],
            ['code'=>'tas','name'=>'Tas','icon'=>'👜','sort_order'=>5],
            ['code'=>'selimut','name'=>'Selimut','icon'=>'🛏️','sort_order'=>6],
            ['code'=>'handuk','name'=>'Handuk','icon'=>'🛁','sort_order'=>7],
            ['code'=>'lainnya','name'=>'Lainnya','icon'=>'📦','sort_order'=>99],
        ];
    }
}
