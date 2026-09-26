<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0f766e">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Londry POS">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
<title>@yield('title','Londry POS') - Londry</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<style>
  /* Tipografi: system font stack, bukan webfont.
     Alasan: POS ini dipakai di koneksi Indonesia yang tidak selalu stabil,
     jadi font harus tampil tanpa menunggu unduhan. System font juga sudah
     punya angka tabular yang benar untuk kolom rupiah. DESIGN.md section 3. */
  body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',sans-serif;-webkit-tap-highlight-color:transparent;font-variant-numeric:tabular-nums}
  /* Nomor order dan SKU adalah identifier yang dibaca per karakter saat
     mencari di tumpukan kertas, jadi pakai tabular/mono. */
  .font-mono{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace}
  *{scrollbar-width:thin;scrollbar-color:#d4ccbf transparent}
  *::-webkit-scrollbar{height:6px;width:6px}
  *::-webkit-scrollbar-thumb{background:#d4ccbf;border-radius:9999px}
  .no-scrollbar::-webkit-scrollbar{display:none}
  .no-scrollbar{scrollbar-width:none}
  @media(max-width:1024px){html{font-size:15px}}
  .nav-link{display:inline-flex;align-items:center;gap:0.75rem;padding:0.625rem 0.75rem;border-radius:6px;text-decoration:none;transition:background-color .15s ease}
  .nav-icon{display:inline-flex;align-items:center;justify-content:center;width:1.25rem;height:1.25rem;flex-shrink:0;overflow:visible}
</style>
@stack('head')
@php
  $__errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
  $__flash = [];
  if (session('success'))  $__flash[] = ['ok',   session('success')];
  if (session('error'))    $__flash[] = ['err',  session('error')];
  if (session('warning'))  $__flash[] = ['warn', session('warning')];
  if (session('info'))     $__flash[] = ['info', session('info')];
  if ($__errors->any())     $__flash[] = ['err',  $__errors->first()];
@endphp
@if(count($__flash))
<div id="flashStack" class="fixed top-3 right-3 left-3 sm:left-auto z-[60] flex flex-col gap-2 sm:w-96" role="status" aria-live="polite">
@foreach($__flash as [$k,$m])
  @php
    $__flashStyle = [
      'ok'   => ['bg-emerald-50 border-emerald-300 text-emerald-900', 'M5 13l4 4L19 7'],
      'err'  => ['bg-rose-50 border-rose-300 text-rose-900',         'M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z'],
      'warn' => ['bg-amber-50 border-amber-300 text-amber-900',      'M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z'],
      'info' => ['bg-sky-50 border-sky-300 text-sky-900',            'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ][$k] ?? ['bg-paper-100 border-paper-400 text-slate-900', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
  @endphp
  <div class="flash-item flex items-start gap-2.5 rounded-xl border px-3.5 py-3 text-sm shadow-lg {{ $__flashStyle[0] }}" data-flash>
    <svg class="flex-shrink-0 mt-px" style="width:1.1rem;height:1.1rem;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $__flashStyle[1] }}"/></svg>
    <p class="flex-1 min-w-0 leading-snug">{{ $m }}</p>
    <button type="button" onclick="this.closest('[data-flash]').remove()" class="flex-shrink-0 -mr-1 -mt-0.5 w-6 h-6 grid place-items-center rounded-lg hover:bg-black/5" aria-label="Tutup notifikasi">
      <svg style="width:.9rem;height:.9rem;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
  </div>
@endforeach
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  // Auto-dismiss non-error toasts; errors stay until dismissed.
  document.querySelectorAll('#flashStack [data-flash]').forEach(function(el){
    var isError = el.className.indexOf('rose') !== -1 || el.className.indexOf('amber') !== -1;
    if(isError) return;
    setTimeout(function(){ if(el.isConnected) el.remove(); }, 6000);
  });
});
</script>
@endif
</head>
<body class="bg-paper-100 text-paper-800 antialiased overflow-x-hidden">
<div class="flex min-h-screen">

  {{-- Desktop sidebar --}}
  <aside id="sidebar" class="w-64 bg-paper-900 text-paper-on-dark hidden lg:flex flex-col fixed inset-y-0 z-30">
    <div class="px-6 py-5 border-b border-paper-800 flex items-center gap-3">
      <div class="w-9 h-9 bg-teal-500 rounded-xl flex items-center justify-center font-bold text-white shrink-0">L</div>
      <div class="min-w-0"><div class="font-semibold text-white truncate">{{ setting('app_name','Londry POS') }}</div><div class="text-xs text-paper-on-dark truncate">{{ setting('company_name','Londry') }}</div></div>
    </div>
    <div class="px-4 py-3 border-b border-paper-800/50 space-y-3">
      @php
        $mid = session('merchant_id') ?? auth()->user()->merchant_id;
        $bid = session('branch_id');
        $isSuperSide = auth()->user()->isAdmin() && auth()->user()->merchant_id === null;
        $merchantsSide = $isSuperSide ? \App\Models\Merchant::orderBy('name')->get() : \App\Models\Merchant::where('id',$mid)->get();
        $branches = auth()->user()->isAdmin()
          ? \App\Models\Branch::when($mid, fn($q)=>$q->where('merchant_id',$mid))->get()
          : (auth()->user()->branches->merge(collect(auth()->user()->branch ? [auth()->user()->branch] : []))->unique('id')->filter(fn($b)=>!$mid || (int)$b->merchant_id === (int)$mid));
      @endphp
      @if($merchantsSide->count() > 1)
      <div>
        <label class="text-[10px] uppercase tracking-widest text-paper-on-dark font-semibold">Merchant / Toko</label>
        <select onchange="location.href='/switch-merchant/'+this.value" class="mt-1.5 w-full bg-paper-800 border border-paper-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
          @foreach($merchantsSide as $m)<option value="{{ $m->id }}" @selected($mid==$m->id)>{{ $m->code }} - {{ $m->name }}</option>@endforeach
        </select>
      </div>
      @elseif($merchantsSide->count()==1)
        <div class="text-xs text-paper-on-dark truncate">{{ $merchantsSide->first()->code }}: {{ $merchantsSide->first()->name }}</div>
      @endif
      <div>
        <label class="text-[10px] uppercase tracking-widest text-paper-on-dark font-semibold">Cabang Aktif</label>
        <select onchange="location.href='/switch-branch/'+this.value" class="mt-1.5 w-full bg-paper-800 border border-paper-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
          @foreach($branches as $b)<option value="{{ $b->id }}" @selected($bid==$b->id)>{{ $b->code }} - {{ $b->name }}</option>@endforeach
        </select>
      </div>
    </div>
    <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto text-sm no-scrollbar">
      <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('dashboard') !!}</span><span>Dashboard</span></a>
      <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('pos') !!}</span><span>POS Kasir</span></a>
      <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('orders') !!}</span><span>Orders</span></a>
      <a href="{{ route('queue.index') }}" class="nav-link {{ request()->routeIs('queue.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('queue') !!}</span><span>Antrian</span></a>
      <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('customers') !!}</span><span>Customers</span></a>
      <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('products') !!}</span><span>Produk</span></a>
      <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('categories') !!}</span><span>Kategori</span></a>
      <a href="{{ route('cash.index') }}" class="nav-link {{ request()->routeIs('cash.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('cash') !!}</span><span>Kas</span></a>
      <a href="{{ route('shifts.index') }}" class="nav-link {{ request()->routeIs('shifts.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('shifts') !!}</span><span>Shift</span></a>
      <div class="pt-3 mt-3 border-t border-paper-800">
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('reports') !!}</span><span>Laporan</span></a>
      </div>
      @if(auth()->user()->isAdmin())
      <div class="pt-3 mt-3 border-t border-paper-800">
        <p class="px-3 text-[10px] uppercase tracking-widest text-paper-on-dark font-semibold mb-1">Admin</p>
        <a href="{{ route('merchants.index') }}" class="nav-link {{ request()->routeIs('merchants.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('merchants') !!}</span><span>Merchant</span></a>
        <a href="{{ route('branches.index') }}" class="nav-link {{ request()->routeIs('branches.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('branches') !!}</span><span>Cabang</span></a>
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('users') !!}</span><span>Users</span></a>
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*')?'bg-teal-600 text-white shadow':'hover:bg-paper-800 text-paper-on-dark' }}"><span class="nav-icon">{!! icon('settings') !!}</span><span>Settings</span></a>
      </div>
      @endif
    </nav>
    <div class="p-4 border-t border-paper-800">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-paper-700 rounded-full flex items-center justify-center text-sm font-semibold shrink-0">{{ substr(auth()->user()->name,0,1) }}</div>
        <div class="flex-1 min-w-0"><div class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</div><div class="text-xs text-paper-on-dark truncate">{{ auth()->user()->roles->pluck('name')->join(', ') }}</div></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-8 h-8 grid place-items-center rounded-lg hover:bg-paper-800 text-paper-on-dark hover:text-white" title="Logout">{!! icon('logout') !!}</button></form>
      </div>
    </div>
  </aside>

  {{-- Mobile drawer --}}
  <div id="drawerOverlay" onclick="closeDrawer()" class="fixed inset-0 bg-paper-900/60 backdrop-blur-sm z-40 hidden lg:hidden"></div>
  <aside id="mobileDrawer" class="fixed inset-y-0 left-0 w-[84%] max-w-[320px] bg-paper-900 text-slate-200 z-50 translate-x-[-100%] transition-transform duration-300 lg:hidden flex flex-col">
    <div class="px-5 py-4 border-b border-paper-800 flex items-center justify-between">
      <div class="flex items-center gap-3"><div class="w-9 h-9 bg-teal-500 rounded-xl flex items-center justify-center font-bold text-white">L</div><div><div class="font-semibold text-white text-sm">{{ setting('app_name','Londry POS') }}</div><div class="text-xs text-paper-500">{{ setting('company_name','Londry') }}</div></div></div>
      <button onclick="closeDrawer()" class="w-9 h-9 grid place-items-center rounded-xl hover:bg-paper-800 text-paper-500">{!! icon('close') !!}</button>
    </div>
    <div class="px-4 py-3 border-b border-paper-800">
      @php $mid2 = session('merchant_id') ?? auth()->user()->merchant_id; $bid2 = session('branch_id'); $branches2 = auth()->user()->isAdmin() ? \App\Models\Branch::when($mid2, fn($q)=>$q->where('merchant_id',$mid2))->get() : (auth()->user()->branches->merge(collect(auth()->user()->branch ? [auth()->user()->branch] : []))->unique('id')->filter(fn($b)=>!$mid2 || (int)$b->merchant_id===(int)$mid2)); $isSuper2 = auth()->user()->isAdmin() && auth()->user()->merchant_id===null; $merchants2 = $isSuper2 ? \App\Models\Merchant::orderBy('name')->get() : \App\Models\Merchant::where('id',$mid2)->get(); @endphp
      @if($merchants2->count()>1)
        <label class="text-[10px] uppercase tracking-widest text-paper-500 font-semibold">Merchant</label>
        <select onchange="location.href='/switch-merchant/'+this.value" class="mt-1.5 w-full bg-paper-800 border border-paper-700 rounded-xl px-3 py-2.5 text-sm text-white mb-2">
          @foreach($merchants2 as $m)<option value="{{ $m->id }}" @selected($mid2==$m->id)>{{ $m->code }} - {{ $m->name }}</option>@endforeach
        </select>
      @endif
      <label class="text-[10px] uppercase tracking-widest text-paper-500 font-semibold">Cabang Aktif</label>
      <select onchange="location.href='/switch-branch/'+this.value" class="mt-1.5 w-full bg-paper-800 border border-paper-700 rounded-xl px-3 py-2.5 text-sm text-white">
        @foreach($branches2 as $b)<option value="{{ $b->id }}" @selected($bid2==$b->id)>{{ $b->code }} - {{ $b->name }}</option>@endforeach
      </select>
    </div>
    <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto text-[15px] no-scrollbar">
      <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('dashboard') !!}</span>Dashboard</a>
      <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('pos') !!}</span>POS Kasir</a>
      <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('orders') !!}</span>Orders</a>
      <a href="{{ route('queue.index') }}" class="nav-link {{ request()->routeIs('queue.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('queue') !!}</span>Antrian</a>
      <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('customers') !!}</span>Customers</a>
      <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('products') !!}</span>Produk</a>
      <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('categories') !!}</span>Kategori</a>
      <a href="{{ route('cash.index') }}" class="nav-link {{ request()->routeIs('cash.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('cash') !!}</span>Kas</a>
      <a href="{{ route('shifts.index') }}" class="nav-link {{ request()->routeIs('shifts.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('shifts') !!}</span>Shift</a>
      <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*')?'bg-teal-600 text-white':'hover:bg-paper-800' }}"><span class="nav-icon">{!! icon('reports') !!}</span>Laporan</a>
      @if(auth()->user()->isAdmin())
      <div class="pt-3 mt-3 border-t border-paper-800 space-y-1">
        <a href="{{ route('merchants.index') }}" class="nav-link hover:bg-paper-800"><span class="nav-icon">{!! icon('merchants') !!}</span>Merchant</a>
        <a href="{{ route('branches.index') }}" class="nav-link hover:bg-paper-800"><span class="nav-icon">{!! icon('branches') !!}</span>Cabang</a>
        <a href="{{ route('users.index') }}" class="nav-link hover:bg-paper-800"><span class="nav-icon">{!! icon('users') !!}</span>Users</a>
        <a href="{{ route('settings.index') }}" class="nav-link hover:bg-paper-800"><span class="nav-icon">{!! icon('settings') !!}</span>Settings</a>
      </div>
      @endif
    </nav>
    <div class="p-4 border-t border-paper-800">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-paper-700 rounded-full grid place-items-center font-semibold">{{ substr(auth()->user()->name,0,1) }}</div>
        <div class="flex-1 min-w-0"><div class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</div><div class="text-xs text-paper-500 truncate">{{ auth()->user()->roles->pluck('name')->join(', ') }}</div></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-9 h-9 grid place-items-center rounded-xl bg-paper-800 text-slate-300" title="Logout">{!! icon('logout') !!}</button></form>
      </div>
    </div>
  </aside>

  {{-- Main --}}
  <div class="flex-1 lg:ml-64 min-w-0 flex flex-col">
    {{-- Mobile topbar --}}
    <header class="lg:hidden bg-white/95 backdrop-blur border-b border-paper-300 sticky top-0 z-20">
      <div class="flex items-center gap-2 px-3 py-2.5">
        <button onclick="openDrawer()" class="w-10 h-10 grid place-items-center rounded-xl bg-paper-900 text-white shrink-0" aria-label="Menu">{!! icon('menu') !!}</button>
        <div class="flex-1 min-w-0 flex items-center gap-2">
          <div class="w-8 h-8 bg-teal-600 rounded-lg grid place-items-center text-white font-bold text-sm shrink-0">L</div>
          <div class="min-w-0"><div class="font-semibold text-sm leading-none truncate">{{ setting('app_name','Londry') }}</div><div class="text-[11px] text-paper-500 truncate">{{ current_branch()? current_branch()->code.' • '.current_branch()->name : 'Pilih cabang' }}</div></div>
        </div>
        <a href="{{ route('pos.index') }}" class="shrink-0 bg-teal-600 active:bg-teal-700 text-white px-3.5 py-2 rounded-xl text-sm font-semibold flex items-center gap-1.5">{!! icon('pos', ['size' => 18]) !!} POS</a>
      </div>
    </header>

    <main class="flex-1 p-3 sm:p-4 lg:p-6 pb-24 lg:pb-6 w-full max-w-full overflow-x-hidden">
      @yield('content')
    </main>

    {{-- Mobile bottom nav --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-paper-300 z-20 safe-pb">
      <div class="grid grid-cols-5 gap-1 px-1 py-1">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-2.5 rounded-xl {{ request()->routeIs('dashboard')?'text-teal-600 bg-teal-50':'text-paper-500' }}"><span class="text-[18px] leading-none">{!! icon('dashboard',['size' => 20]) !!}</span><span class="text-[10px] font-medium mt-1">Home</span></a>
        <a href="{{ route('pos.index') }}" class="flex flex-col items-center justify-center py-2.5 rounded-xl {{ request()->routeIs('pos.*')?'text-teal-600 bg-teal-50':'text-paper-500' }}"><span class="text-[18px] leading-none">{!! icon('pos',['size' => 20]) !!}</span><span class="text-[10px] font-medium mt-1">POS</span></a>
        <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center py-2.5 rounded-xl {{ request()->routeIs('orders.*')?'text-teal-600 bg-teal-50':'text-paper-500' }}"><span class="text-[18px] leading-none">{!! icon('orders',['size' => 20]) !!}</span><span class="text-[10px] font-medium mt-1">Orders</span></a>
        <a href="{{ route('customers.index') }}" class="flex flex-col items-center justify-center py-2.5 rounded-xl {{ request()->routeIs('customers.*')?'text-teal-600 bg-teal-50':'text-paper-500' }}"><span class="text-[18px] leading-none">{!! icon('customers',['size' => 20]) !!}</span><span class="text-[10px] font-medium mt-1">Cust</span></a>
        <button onclick="openDrawer()" class="flex flex-col items-center justify-center py-2.5 rounded-xl text-paper-500"><span class="text-[18px] leading-none">{!! icon('menu',['size' => 20]) !!}</span><span class="text-[10px] font-medium mt-1">Menu</span></button>
      </div>
    </nav>
  </div>
</div>

<script>
function openDrawer(){document.getElementById('mobileDrawer').style.transform='translateX(0)';document.getElementById('drawerOverlay').classList.remove('hidden');document.body.style.overflow='hidden'}
function closeDrawer(){document.getElementById('mobileDrawer').style.transform='translateX(-100%)';document.getElementById('drawerOverlay').classList.add('hidden');document.body.style.overflow=''}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeDrawer()});
</script>
<style>.safe-pb{padding-bottom:max(0.25rem,env(safe-area-inset-bottom))}</style>
@stack('scripts')
</body>
</html>
