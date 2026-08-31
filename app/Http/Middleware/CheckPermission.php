<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class CheckPermission {
    public function handle(Request $request, Closure $next, $permission){
        $user = $request->user();
        if(!$user) abort(403,'Unauthorized');
        if($user->isAdmin()) return $next($request);
        if(!$user->hasPermission($permission)) abort(403,'Forbidden: need '.$permission);
        return $next($request);
    }
}
