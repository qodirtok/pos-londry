<?php
namespace App\Support;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
class AuditLogger {
    public static function log(string $action, string $module, $referenceType=null, $referenceId=null, $old=null, $new=null){
        $user = Auth::user();
        AuditLog::create([
            'user_id'=> $user?->id,
            'branch_id'=> $user?->branch_id ?? session('branch_id'),
            'action'=>$action,'module'=>$module,
            'reference_type'=>$referenceType,'reference_id'=>$referenceId,
            'old_value'=> $old ? json_encode($old) : null,
            'new_value'=> $new ? json_encode($new) : null,
            'ip_address'=> Request::ip(),'user_agent'=> Request::userAgent(),
        ]);
    }
}
