<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    protected $fillable = ['sku','barcode','name','category_id','type','price','cost','unit','status','description','merchant_id'];
    protected $casts = ['price'=>'decimal:2','cost'=>'decimal:2'];
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function category(){ return $this->belongsTo(Category::class); }
    public function stocks(){ return $this->hasMany(ProductStock::class); }
    public function scopeActive($q){ return $q->where('status','active'); }
    public function scopeForBranch($q,$branchId){ return $q; }
}
