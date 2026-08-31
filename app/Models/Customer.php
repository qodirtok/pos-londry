<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {
    protected $fillable = ['code','name','phone','email','address','notes','status','branch_id','merchant_id','is_demo'];
    protected $casts = ['is_demo'=>'boolean'];
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function orders(){ return $this->hasMany(Order::class); }
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function scopeForBranch($q,$branchId){ return $q->where('branch_id',$branchId); }
}
