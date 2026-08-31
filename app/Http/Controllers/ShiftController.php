<?php
namespace App\Http\Controllers;
use App\Models\CashierShift;
use App\Services\CashService;
use Illuminate\Http\Request;
class ShiftController extends Controller {
    public function index(){
        $mid=auth()->user()->merchant_id; $shifts=CashierShift::with(['branch','cashier'])->when(session('branch_id'), fn($q,$id)=>$q->where('branch_id',$id))->when($mid, fn($q)=>$q->where('merchant_id',$mid))->latest()->paginate(10);
        $open=CashierShift::where('cashier_id',auth()->id())->when(auth()->user()->merchant_id, fn($q)=>$q->where('merchant_id', auth()->user()->merchant_id))->where('status','open')->first();
        return view('shifts.index', compact('shifts','open'));
    }
    public function open(Request $r, CashService $svc){
        $r->validate(['opening_cash'=>'required|numeric|min:0']);
        $svc->openShift(session('branch_id')??auth()->user()->branch_id, auth()->id(), $r->opening_cash, $r->notes);
        return back()->with('success','Shift dibuka');
    }
    public function close(Request $r, CashierShift $shift, CashService $svc){
        $r->validate(['actual_cash'=>'required|numeric|min:0']);
        if($shift->cashier_id!==auth()->id() && !auth()->user()->isAdmin()) abort(403);
        $svc->closeShift($shift, $r->actual_cash);
        return back()->with('success','Shift ditutup. Selisih: '.money($shift->fresh()->difference));
    }
}
