<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Branch extends Model {
    protected $fillable = ['code','name','phone','email','address','city','province','postal_code','status','is_demo','merchant_id'];
    protected $casts = ['status'=>'string','is_demo'=>'boolean'];
    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'branch_user'); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function scopeActive($q){ return $q->where('status','active'); }
}
