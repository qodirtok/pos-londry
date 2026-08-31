<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
    public function showLogin(){ if(Auth::check()) return redirect('/dashboard'); return view('auth.login'); }
    public function login(Request $request){
        $request->validate(['username_or_email'=>'required','password'=>'required']);
        $field = filter_var($request->username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if(!Auth::attempt([$field=>$request->username_or_email,'password'=>$request->password], $request->boolean('remember'))){
            // also try email fallback if username field used
            if($field==='username' && Auth::attempt(['email'=>$request->username_or_email,'password'=>$request->password], $request->boolean('remember'))){}
            else return back()->withErrors(['username_or_email'=>'Kredensial salah'])->withInput();
        }
        $user = Auth::user();
        if($user->status!=='active'){ Auth::logout(); return back()->withErrors(['username_or_email'=>'Akun nonaktif']); }
        $user->update(['last_login_at'=>now()]);
        $request->session()->regenerate();
        // set merchant + branch session
        if($user->merchant_id) session(['merchant_id'=>$user->merchant_id]);
        else if($user->branches()->exists()) {
            $b = $user->branches()->first();
            if($b && $b->merchant_id) session(['merchant_id'=>$b->merchant_id]);
        }
        if($user->branch_id) session(['branch_id'=>$user->branch_id]);
        else if($user->branches()->exists()) session(['branch_id'=>$user->branches()->first()->id]);
        return redirect()->intended('/dashboard');
    }
    public function logout(Request $request){
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect('/login');
    }
    public function switchBranch(Request $request, $id){
        $user = auth()->user();
        $branch = \App\Models\Branch::findOrFail($id);
        if(!$user->isAdmin()){
            $allowed = $user->branches()->pluck('branches.id')->toArray();
            if($user->branch_id) $allowed[]=$user->branch_id;
            if(!in_array($branch->id, array_unique($allowed))) abort(403,'Tidak punya akses cabang ini');
        }
        // merchant isolation: cannot switch to branch of other merchant (unless super admin tanpa merchant)
        if($user->merchant_id && $branch->merchant_id && (int)$branch->merchant_id !== (int)$user->merchant_id){
            abort(403,'Cabang bukan milik merchant Anda');
        }
        session(['branch_id'=>$branch->id]);
        if($branch->merchant_id) session(['merchant_id'=>$branch->merchant_id]);
        return back()->with('success','Cabang beralih ke '.$branch->name);
    }
    public function switchMerchant(Request $request, $id){
        $user = auth()->user();
        if(!$user->isAdmin()) abort(403,'Hanya Admin bisa ganti merchant');
        $merchant = \App\Models\Merchant::findOrFail($id);
        // non-super admin (punya merchant_id) hanya boleh ke merchant sendiri
        if($user->merchant_id && (int)$merchant->id !== (int)$user->merchant_id) abort(403,'Bukan merchant Anda');
        session(['merchant_id'=>$merchant->id]);
        // auto set branch ke cabang pertama merchant tersebut
        $branchId = \App\Models\Branch::where('merchant_id',$merchant->id)->value('id');
        if($branchId) session(['branch_id'=>$branchId]);
        return back()->with('success','Merchant beralih ke '.$merchant->name);
    }
}
