<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model {
    protected $fillable = ['product_id','branch_id','user_id','type','quantity','old_quantity','new_quantity','difference','reason'];
    protected $casts = ['quantity'=>'decimal:3','old_quantity'=>'decimal:3','new_quantity'=>'decimal:3','difference'=>'decimal:3'];
    public function product(){ return $this->belongsTo(Product::class); }
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
