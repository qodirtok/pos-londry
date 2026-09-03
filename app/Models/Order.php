<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
    protected $fillable = ['order_number','branch_id','merchant_id','customer_id','cashier_id','order_date','pickup_date','completed_at','subtotal','discount','discount_type','tax','total','paid_amount','change_amount','payment_status','order_status','notes','laundry_details','cancelled_at','cancelled_by','cancel_reason','is_demo'];
    protected $casts = [
        'order_date'=>'date','pickup_date'=>'date','completed_at'=>'datetime','cancelled_at'=>'datetime',
        'subtotal'=>'decimal:2','discount'=>'decimal:2','tax'=>'decimal:2','total'=>'decimal:2','paid_amount'=>'decimal:2','change_amount'=>'decimal:2',
        'laundry_details'=>'array','is_demo'=>'boolean',
    ];
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function cashier(){ return $this->belongsTo(User::class,'cashier_id'); }
    public function items(){ return $this->hasMany(OrderItem::class); }
    public function payments(){ return $this->hasMany(Payment::class); }
    public function refunds(){ return $this->hasMany(Refund::class); }
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function scopeForBranch($q,$branchId){ return $q->where('branch_id',$branchId); }
    /** True if this is a laundry order (has laundry_details or at least one service item). Checked via relation load when available. */
    public function isLaundry(): bool {
        if(!empty($this->laundry_details)) return true;
        if($this->relationLoaded('items')){
            foreach($this->items as $it) if(($it->product?->type)==='service' || ($it->type??'')==='service') return true;
        }
        return false;
    }

    public function laundrySummary(): string {
        if(empty($this->laundry_details)) return $this->notes ?? '';
        $d=$this->laundry_details;
        $catatan = $d['catatan'] ?? null;
        $lainnyaDesc = $d['lainnya_desc'] ?? null;
        $items = array_filter($d, fn($k)=> !in_array($k, ['catatan','lainnya_desc']), ARRAY_FILTER_USE_KEY);
        // map code -> name from DB
        $map = [];
        if(!empty($items)){
            try { $map = LaundryItemType::whereIn('code', array_keys($items))->pluck('name','code')->toArray(); } catch(\Throwable $e) {}
        }
        $parts=[];
        foreach($items as $k=>$v){
            if($v && $v!=='0' && $v!=='') {
                $label = $map[$k] ?? ucfirst(str_replace('_',' ',$k));
                $parts[]="$label: $v pcs";
            }
        }
        if(!empty($lainnyaDesc)) $parts[]="Ket. Lainnya: ".$lainnyaDesc;
        if(!empty($catatan)) $parts[]="Catatan: ".$catatan;
        return implode(' | ', $parts);
    }
}
