<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Category extends Model {
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    protected $fillable = ['name','code','description','status','merchant_id'];
    public function products(){ return $this->hasMany(Product::class); }
}
