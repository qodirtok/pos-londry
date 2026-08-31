<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use App\Models\Branch;
use Illuminate\Http\Request;
class SettingController extends Controller {
    public function index(){
        $mid=auth()->user()->merchant_id;
        $settings=Setting::when($mid, fn($q)=>$q->where('merchant_id',$mid))->whereNull('branch_id')->get()->keyBy('key');
        $branches= $mid ? Branch::where('merchant_id',$mid)->get() : Branch::all();
        return view('settings.index', compact('settings','branches'));
    }
    public function update(Request $r){
        $mid=auth()->user()->merchant_id;
        foreach($r->except('_token') as $key=>$value){
            if(in_array($key,['branch_id'])) continue;
            Setting::updateOrCreate(['branch_id'=>null,'merchant_id'=>$mid,'key'=>$key], ['value'=>$value,'type'=>'string']);
        }
        return back()->with('success','Setting disimpan');
    }
}
