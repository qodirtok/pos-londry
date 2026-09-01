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
if(!function_exists('icon')){
    function icon(string $name, array $attrs = []): string {
        // Simple emoji/emoticon icons that work reliably on all devices
        $icons = [
            'dashboard' => '📊',
            'pos' => '🧾',
            'orders' => '📋',
            'customers' => '👥',
            'products' => '📦',
            'categories' => '📁',
            'cash' => '💰',
            'shifts' => '⏰',
            'reports' => '📈',
            'laundry' => '🧺',
            'merchants' => '🏪',
            'branches' => '🏢',
            'users' => '👤',
            'settings' => '⚙️',
            'close' => '✖️',
            'menu' => '☰',
            'save' => '💾',
            'cancel' => '❌',
            'print' => '🖨️',
            'whatsapp' => '💬',
            'edit' => '✏️',
            'logout' => '🚪',
        ];
        $emoji = $icons[$name] ?? $icons['dashboard'];
        $size = $attrs['size'] ?? 20;
        $style = '';
        if (isset($attrs['style'])) {
            $style = ' style="' . $attrs['style'] . '"';
        }
        return '<span class="emoji-icon" style="font-size: ' . $size . 'px; line-height: 1;"' . $style . ' role="img" aria-label="' . $name . '">' . $emoji . '</span>';
    }
}
