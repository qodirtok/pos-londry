<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller {
    public function index(Request $r){
        $isDemo=(bool)auth()->user()->is_demo;
        $users = User::with(['roles','branch'])->where('is_demo',$isDemo)
            ->when(auth()->user()->merchant_id, fn($q)=>$q->where('merchant_id', auth()->user()->merchant_id))
            ->latest()->paginate(10);
        return view('users.index',compact('users'));
    }
    public function create(){ 
        $user = auth()->user();
        $roles=Role::all(); 
        $branches= $user->merchant_id ? Branch::where('merchant_id',$user->merchant_id)->get() : Branch::all(); 
        return view('users.create',compact('roles','branches')); 
    }
    public function store(Request $r){
        $r->validate(['name'=>'required','username'=>'required|unique:users,username','email'=>'required|email|unique:users,email','password'=>'required|min:6','branch_id'=>'nullable|exists:branches,id','status'=>'required|in:active,inactive']);
        $branchForUser = $r->branch_id ? Branch::find($r->branch_id) : null;
        if($branchForUser && auth()->user()->merchant_id && (int)$branchForUser->merchant_id !== (int)auth()->user()->merchant_id) abort(403,'Cabang bukan merchant Anda');
        $mid = auth()->user()->merchant_id ?? ($branchForUser?->merchant_id ?? Branch::first()?->merchant_id);
        $user = User::create(['name'=>$r->name,'username'=>$r->username,'email'=>$r->email,'password'=>Hash::make($r->password),'phone'=>$r->phone,'status'=>$r->status,'branch_id'=>$r->branch_id,'merchant_id'=>$mid,'is_demo'=>(bool)auth()->user()->is_demo]);
        if($r->filled('roles')) $user->roles()->sync($r->roles);
        if($r->filled('branch_ids')) $user->branches()->sync($r->branch_ids);
        return redirect()->route('users.index')->with('success','User dibuat');
    }
    public function show(User $user){ $this->authorizeUser($user); return redirect()->route('users.edit',$user); }
    public function edit(User $user){ $this->authorizeUser($user); $roles=Role::all(); $branches= auth()->user()->merchant_id ? Branch::where('merchant_id',auth()->user()->merchant_id)->get() : Branch::all(); return view('users.edit',compact('user','roles','branches')); }
    public function update(Request $r, User $user){
        $this->authorizeUser($user);
        $r->validate(['name'=>'required','username'=>'required|unique:users,username,'.$user->id,'email'=>'required|email|unique:users,email,'.$user->id,'branch_id'=>'nullable|exists:branches,id','status'=>'required|in:active,inactive']);
        if($r->filled('branch_id')){ $b=Branch::find($r->branch_id); if($b && auth()->user()->merchant_id && (int)$b->merchant_id !== (int)auth()->user()->merchant_id) abort(403,'Cabang bukan merchant Anda'); }
        $data=$r->only(['name','username','email','phone','status','branch_id']);
        if($r->filled('password')) $data['password']=Hash::make($r->password);
        $user->update($data);
        if($r->has('roles')) $user->roles()->sync($r->roles);
        if($r->has('branch_ids')) $user->branches()->sync($r->branch_ids??[]);
        return redirect()->route('users.index')->with('success','User diupdate');
    }
    public function destroy(User $user){ $this->authorizeUser($user); $user->delete(); return back()->with('success','Dihapus'); }
    private function authorizeUser(User $u){
        $me = auth()->user();
        if((bool)$u->is_demo !== (bool)$me->is_demo) abort(403);
        if($me->merchant_id && (int)$u->merchant_id !== (int)$me->merchant_id) abort(403,'Bukan user merchant Anda');
    }
}
