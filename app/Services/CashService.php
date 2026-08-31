<?php
namespace App\Services;
use App\Models\CashTransaction;
use App\Models\CashierShift;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
class CashService {
    public function openShift($branchId,$cashierId,$openingCash,$notes=null){
        if(CashierShift::where('cashier_id',$cashierId)->where('status','open')->exists()){
            throw new \Exception('Shift masih open, tutup dulu');
        }
        $branch = \App\Models\Branch::find($branchId);
        $mid = $branch?->merchant_id ?? (auth()->check() ? auth()->user()->merchant_id : null);
        $shift = CashierShift::create([
            'branch_id'=>$branchId,'merchant_id'=>$mid,'cashier_id'=>$cashierId,'opened_at'=>now(),
            'opening_cash'=>$openingCash,'status'=>'open','notes'=>$notes
        ]);
        AuditLogger::log('open_shift','cashier_shifts','CashierShift',$shift->id,null,$shift->toArray());
        return $shift;
    }
    public function closeShift(CashierShift $shift, $actualCash){
        if($shift->status==='closed') throw new \Exception('Shift sudah closed');
        $cashSales = CashTransaction::where('branch_id',$shift->branch_id)->when($shift->merchant_id, fn($q)=>$q->where('merchant_id',$shift->merchant_id))->where('type','income')->where('category','Penjualan')->whereBetween('created_at',[$shift->opened_at, now()])->sum('amount');
        $cashIncome = CashTransaction::where('branch_id',$shift->branch_id)->when($shift->merchant_id, fn($q)=>$q->where('merchant_id',$shift->merchant_id))->where('type','income')->where('category','!=','Penjualan')->whereBetween('created_at',[$shift->opened_at, now()])->sum('amount');
        $cashExpense = CashTransaction::where('branch_id',$shift->branch_id)->when($shift->merchant_id, fn($q)=>$q->where('merchant_id',$shift->merchant_id))->where('type','expense')->whereBetween('created_at',[$shift->opened_at, now()])->sum('amount');
        $expected = (float)$shift->opening_cash + (float)$cashSales + (float)$cashIncome - (float)$cashExpense;
        $difference = (float)$actualCash - $expected;
        $shift->update(['closed_at'=>now(),'expected_cash'=>$expected,'actual_cash'=>$actualCash,'difference'=>$difference,'status'=>'closed']);
        AuditLogger::log('close_shift','cashier_shifts','CashierShift',$shift->id,null,$shift->toArray());
        return $shift;
    }
}
