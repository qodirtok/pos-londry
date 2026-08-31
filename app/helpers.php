<?php
use App\Models\Setting;
if(!function_exists('setting')){
    function setting(string $key, $default=null, $branchId=null){
        try { return Setting::get($key,$default,$branchId); } catch (\Throwable $e){ return $default; }
    }
}
if(!function_exists('money')){
    function money($value){ return 'Rp ' . number_format((float)$value,0,',','.'); }
}
if(!function_exists('current_branch')){
    function current_branch(){
        $bid = session('branch_id');
        if(!$bid) return null;
        return \App\Models\Branch::find($bid);
    }
}
