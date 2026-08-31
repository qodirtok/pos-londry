<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Merchant extends Model {
    protected $fillable = ['code','name','slug','phone','email','address','city','status','owner_user_id'];
    public function owner(): BelongsTo { return $this->belongsTo(User::class,'owner_user_id'); }
    public function branches(): HasMany { return $this->hasMany(Branch::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
}
