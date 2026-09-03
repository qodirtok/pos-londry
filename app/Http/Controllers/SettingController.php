<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
    public function backup(){
        $user = auth()->user();
        if (!$user || !$user->hasRole('Admin')) {
            abort(403, 'Hanya Admin yang dapat melakukan backup database.');
        }
        $cfg = config('database.connections.mysql');
        $db  = $cfg['database'];
        $ts  = now()->format('Y-m-d_His');
        Storage::makeDirectory('backups');
        $file = 'backups/db_backup_'.$ts.'.sql';
        $path = storage_path('app/'.$file);

        // resolve mysqldump binary path (PATH may not include brew mysql-client under web/PHP process)
        $mysqldump = null;
        foreach (['/usr/bin/mysqldump','/usr/local/mysql/bin/mysqldump','/opt/homebrew/opt/mysql-client/bin/mysqldump'] as $c) {
            if (@file_exists($c)) { $mysqldump = $c; break; }
        }
        if (!$mysqldump) {
            $probe = @shell_exec('command -v mysqldump 2>/dev/null');
            if ($probe && @file_exists(trim($probe))) $mysqldump = trim($probe);
        }
        if (!$mysqldump) {
            return back()->with('error','Backup gagal: mysqldump binary tidak ditemukan. Pasang mysql-client atau setting path.');
        }

        $cmd = sprintf('%s --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($mysqldump),
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['port']),
            escapeshellarg($cfg['username']),
            $cfg['password'],
            escapeshellarg($db),
            escapeshellarg($path)
        );
        $proc = Process::fromShellCommandline($cmd);
        $proc->setTimeout(300);
        try { $proc->mustRun(); } catch (ProcessFailedException $e) {
            if (file_exists($path)) @unlink($path);
            return back()->with('error','Backup gagal: command tidak berhasil dijalankan.');
        }
        if (!file_exists($path) || filesize($path) === 0) {
            if (file_exists($path)) @unlink($path);
            return back()->with('error','Backup gagal: mysqldump mengembalikan file kosong. Periksa kredensial database.');
        }

        $zipPath = str_replace('.sql','.zip',$path);
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            $zip->addFile($path, basename($path));
            $zip->close();
            @unlink($path);
            $file = str_replace('.sql','.zip',$file);
        }
        return Storage::disk('local')->download($file);
    }
}
