<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model {
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function branch(){ return $this->belongsTo(Branch::class); }
    protected $fillable = ['branch_id','merchant_id','key','value','type'];
    public static function get($key, $default=null, $branchId=null){
        $q = static::where('key',$key);
        if($branchId) $q->where('branch_id',$branchId);
        else $q->whereNull('branch_id');
        $s = $q->first();
        return $s ? $s->value : $default;
    }
}
