<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Refund extends Model {
    protected $fillable = ['order_id','refund_number','amount','reason','created_by'];
    protected $casts = ['amount'=>'decimal:2'];
    public function order(){ return $this->belongsTo(Order::class); }
    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
}
