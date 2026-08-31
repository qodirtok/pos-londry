<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Models\Branch;
class BranchContext {
    public function handle(Request $request, Closure $next){
        $user = $request->user();
        if($user){
            $bid = session('branch_id');
            if(!$bid && $user->branch_id){
                session(['branch_id'=>$user->branch_id]);
            }
            // Demo isolation: paksa demo selalu di cabang demo, cegah switch ke cabang produksi
            if($user->is_demo){
                $demoBranchId = \App\Models\Branch::where('is_demo', true)->value('id');
                if($demoBranchId){
                    if(!$bid || (int)$bid !== (int)$demoBranchId){
                        // jika ada branch produksi di session, paksa ke demo
                        $prod = \App\Models\Branch::find($bid);
                        if(!$prod || !(bool)$prod->is_demo){
                            session(['branch_id'=>$demoBranchId]);
                        }
                    }
                }
            }
            // validate branch access for non-admin
            if($bid && !$user->isAdmin()){
                $allowed = $user->branches()->pluck('branches.id')->toArray();
                if($user->branch_id) $allowed[] = $user->branch_id;
                if(!in_array($bid, array_unique($allowed)) && $request->routeIs('branches.*')===false){
                    // keep but allow admin to switch; for kasir redirect to switch page
                }
            }
        }
        return $next($request);
    }
}
