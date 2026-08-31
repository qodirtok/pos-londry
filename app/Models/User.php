<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = ['name','username','email','password','phone','status','branch_id','merchant_id','last_login_at','is_demo'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at'=>'datetime','last_login_at'=>'datetime','password'=>'hashed','is_demo'=>'boolean'];
    public function merchant(){ return $this->belongsTo(Merchant::class); }
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function branches(){ return $this->belongsToMany(Branch::class,'branch_user'); }
    public function roles(){ return $this->belongsToMany(Role::class,'role_user'); }
    public function hasRole($role){ return $this->roles()->where('name',$role)->exists(); }
    public function hasPermission($perm){
        return $this->roles()->whereHas('permissions', fn($q)=>$q->where('name',$perm))->exists();
    }
    public function isAdmin(){ return $this->hasRole('Admin'); }
    public function isDemo(): bool { return (bool)$this->is_demo; }
    public function isMerchantOwner(): bool { return $this->hasRole('Admin') && $this->merchant_id && \App\Models\Merchant::where('owner_user_id',$this->id)->exists(); }
}
