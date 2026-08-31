<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductStock extends Model {
    protected $fillable = ['product_id','branch_id','quantity','minimum_stock'];
    protected $casts = ['quantity'=>'decimal:3','minimum_stock'=>'decimal:3'];
    public function product(){ return $this->belongsTo(Product::class); }
    public function branch(){ return $this->belongsTo(Branch::class); }
}
