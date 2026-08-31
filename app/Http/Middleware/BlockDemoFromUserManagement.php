<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class BlockDemoFromUserManagement {
    public function handle(Request $request, Closure $next){
        $u = $request->user();
        if($u && (bool)$u->is_demo){
            // demo tidak boleh akses manajemen user/branch (melihat & membuat user baru)
            abort(403, 'Akun demo tidak memiliki akses manajemen user/cabang.');
        }
        return $next($request);
    }
}
