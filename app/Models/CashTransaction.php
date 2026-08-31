<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CashTransaction extends Model {
    protected $fillable = ['branch_id','merchant_id','user_id','type','category','amount','reference_type','reference_id','description','transaction_date'];
    protected $casts = ['amount'=>'decimal:2','transaction_date'=>'date'];
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
