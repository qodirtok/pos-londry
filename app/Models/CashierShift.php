<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CashierShift extends Model {
    protected $fillable = ['branch_id','merchant_id','cashier_id','opened_at','closed_at','opening_cash','expected_cash','actual_cash','difference','status','notes'];
    protected $casts = ['opened_at'=>'datetime','closed_at'=>'datetime','opening_cash'=>'decimal:2','expected_cash'=>'decimal:2','actual_cash'=>'decimal:2','difference'=>'decimal:2'];
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function cashier(){ return $this->belongsTo(User::class,'cashier_id'); }
}
