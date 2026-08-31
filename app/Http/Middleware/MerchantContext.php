<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class MerchantContext {
    public function handle(Request $request, Closure $next){
        $user = $request->user();
        if($user){
            if(session()->has('merchant_id') === false && $user->merchant_id){
                session(['merchant_id'=>$user->merchant_id]);
            }
            // Admin tanpa merchant: boleh akses semua (super admin), jangan force
            // User biasa wajib merchant_id match
        }
        return $next($request);
    }
}
