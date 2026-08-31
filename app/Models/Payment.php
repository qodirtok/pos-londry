<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {
    protected $fillable = ['order_id','payment_number','payment_method','amount','paid_at','cashier_id','reference_number','notes','status'];
    protected $casts = ['amount'=>'decimal:2','paid_at'=>'datetime'];
    public function order(){ return $this->belongsTo(Order::class); }
    public function cashier(){ return $this->belongsTo(User::class,'cashier_id'); }
}
