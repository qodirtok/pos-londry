<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#4f46e5">
<title>@yield('title','Londry POS') - Londry</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body{font-family:Inter,system-ui,sans-serif;-webkit-tap-highlight-color:transparent}
  *{scrollbar-width:thin;scrollbar-color:#cbd5e1 transparent}
  *::-webkit-scrollbar{height:6px;width:6px}
  *::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:9999px}
  .no-scrollbar::-webkit-scrollbar{display:none}
  .no-scrollbar{scrollbar-width:none}
  @media(max-width:1024px){html{font-size:15px}}
</style>
@stack('head')
</head>
<body class="bg-slate-50 text-slate-800 antialiased overflow-x-hidden">
<div class="flex min-h-screen">

  {{-- Desktop sidebar --}}
  <aside id="sidebar" class="w-64 bg-slate-900 text-slate-200 hidden lg:flex flex-col fixed inset-y-0 z-30">
    <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-3">
      <div class="w-9 h-9 bg-indigo-500 rounded-xl flex items-center justify-center font-bold text-white shrink-0">L</div>
      <div class="min-w-0"><div class="font-semibold text-white truncate">{{ setting('app_name','Londry POS') }}</div><div class="text-xs text-slate-400 truncate">{{ setting('company_name','Londry') }}</div></div>
    </div>
    <div class="px-4 py-3 border-b border-slate-800/50 space-y-3">
      @php
        $mid = session('merchant_id') ?? auth()->user()->merchant_id;
        $bid = session('branch_id');
        // merchant list: super admin lihat semua, yang lain hanya miliknya
        $isSuperSide = auth()->user()->isAdmin() && auth()->user()->merchant_id === null;
        $merchantsSide = $isSuperSide ? \App\Models\Merchant::orderBy('name')->get() : \App\Models\Merchant::where('id',$mid)->get();
        $branches = auth()->user()->isAdmin()
          ? \App\Models\Branch::when($mid, fn($q)=>$q->where('merchant_id',$mid))->get()
          : (auth()->user()->branches->merge(collect(auth()->user()->branch ? [auth()->user()->branch] : []))->unique('id')->filter(fn($b)=>!$mid || (int)$b->merchant_id === (int)$mid));
      @endphp
      @if($merchantsSide->count() > 1)
      <div>
        <label class="text-[10px] uppercase tracking-widest text-slate-500 font-semibold">Merchant / Toko</label>
        <select onchange="location.href='/switch-merchant/'+this.value" class="mt-1.5 w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          @foreach($merchantsSide as $m)<option value="{{ $m->id }}" @selected($mid==$m->id)>{{ $m->code }} - {{ $m->name }}</option>@endforeach
        </select>
      </div>
      @elseif($merchantsSide->count()==1)
        <div class="text-xs text-slate-400 truncate">{{ $merchantsSide->first()->code }} — {{ $merchantsSide->first()->name }}</div>
      @endif
      <div>
        <label class="text-[10px] uppercase tracking-widest text-slate-500 font-semibold">Cabang Aktif</label>
        <select onchange="location.href='/switch-branch/'+this.value" class="mt-1.5 w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          @foreach($branches as $b)<option value="{{ $b->id }}" @selected($bid==$b->id)>{{ $b->code }} - {{ $b->name }}</option>@endforeach
        </select>
      </div>
    </div>
    <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto text-sm no-scrollbar">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard')?'bg-indigo-600 text-white shadow':'hover:bg-slate-800 text-slate-300' }}">📊 <span>Dashboard</span></a>
      <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('pos.*')?'bg-indigo-600 text-white shadow':'hover:bg-slate-800 text-slate-300' }}">🛒 <span>POS Kasir</span></a>
      <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('orders.*')?'bg-indigo-600 text-white shadow':'hover:bg-slate-800 text-slate-300' }}">📦 <span>Orders</span></a>
      <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('customers.*')?'bg-indigo-600 text-white shadow':'hover:bg-slate-800 text-slate-300' }}">👥 <span>Customers</span></a>
      <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('products.*')?'bg-indigo-600 text-white shadow':'hover:bg-slate-800 text-slate-300' }}">🧴 <span>Produk</span></a>
      <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('categories.*')?'bg-indigo-600 text-white shadow':'hover:bg-slate-800 text-slate-300' }}">🏷️ <span>Kategori</span></a>
      <a href="{{ route('cash.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('cash.*')?'bg-indigo-600 text-white shadow':'hover:bg-slate-800 text-slate-300' }}">💰 <span>Kas</span></a>
      <a href="{{ route('shifts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('shifts.*')?'bg-indigo-600 text-white shadow':'hover:bg-slate-800 text-slate-300' }}">🕒 <span>Shift</span></a>
      <div class="pt-3 mt-3 border-t border-slate-800">
        <p class="px-3 text-[10px] uppercase tracking-widest text-slate-500 font-semibold mb-1">Laporan</p>
        <a href="{{ route('reports.sales') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-800 text-slate-300">📈 Penjualan</a>
        <a href="{{ route('reports.laundry') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-800 text-slate-300">👕 Laundry</a>
        <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-800 text-slate-300">📑 Semua Laporan</a>
      </div>
      @if(auth()->user()->isAdmin())
      <div class="pt-3 mt-3 border-t border-slate-800">
        <p class="px-3 text-[10px] uppercase tracking-widest text-slate-500 font-semibold mb-1">Admin</p>
        <a href="{{ route('merchants.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-800 text-slate-300">🏪 Merchant</a>
        <a href="{{ route('branches.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-800 text-slate-300">🏢 Cabang</a>
        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-800 text-slate-300">👤 Users</a>
        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-800 text-slate-300">⚙️ Settings</a>
      </div>
      @endif
    </nav>
    <div class="p-4 border-t border-slate-800">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-slate-700 rounded-full flex items-center justify-center text-sm font-semibold shrink-0">{{ substr(auth()->user()->name,0,1) }}</div>
        <div class="flex-1 min-w-0"><div class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</div><div class="text-xs text-slate-400 truncate">{{ auth()->user()->roles->pluck('name')->join(', ') }}</div></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-8 h-8 grid place-items-center rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white" title="Logout">↗</button></form>
      </div>
    </div>
  </aside>

  {{-- Mobile drawer --}}
  <div id="drawerOverlay" onclick="closeDrawer()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden lg:hidden"></div>
  <aside id="mobileDrawer" class="fixed inset-y-0 left-0 w-[84%] max-w-[320px] bg-slate-900 text-slate-200 z-50 translate-x-[-100%] transition-transform duration-300 lg:hidden flex flex-col">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
      <div class="flex items-center gap-3"><div class="w-9 h-9 bg-indigo-500 rounded-xl flex items-center justify-center font-bold text-white">L</div><div><div class="font-semibold text-white text-sm">{{ setting('app_name','Londry POS') }}</div><div class="text-xs text-slate-400">{{ setting('company_name','Londry') }}</div></div></div>
      <button onclick="closeDrawer()" class="w-9 h-9 grid place-items-center rounded-xl hover:bg-slate-800 text-slate-400">✕</button>
    </div>
    <div class="px-4 py-3 border-b border-slate-800">
      @php $mid2 = session('merchant_id') ?? auth()->user()->merchant_id; $bid2 = session('branch_id'); $branches2 = auth()->user()->isAdmin() ? \App\Models\Branch::when($mid2, fn($q)=>$q->where('merchant_id',$mid2))->get() : (auth()->user()->branches->merge(collect(auth()->user()->branch ? [auth()->user()->branch] : []))->unique('id')->filter(fn($b)=>!$mid2 || (int)$b->merchant_id===(int)$mid2)); $isSuper2 = auth()->user()->isAdmin() && auth()->user()->merchant_id===null; $merchants2 = $isSuper2 ? \App\Models\Merchant::orderBy('name')->get() : \App\Models\Merchant::where('id',$mid2)->get(); @endphp
      @if($merchants2->count()>1)
        <label class="text-[10px] uppercase tracking-widest text-slate-500 font-semibold">Merchant</label>
        <select onchange="location.href='/switch-merchant/'+this.value" class="mt-1.5 w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white mb-2">
          @foreach($merchants2 as $m)<option value="{{ $m->id }}" @selected($mid2==$m->id)>{{ $m->code }} - {{ $m->name }}</option>@endforeach
        </select>
      @endif
      <label class="text-[10px] uppercase tracking-widest text-slate-500 font-semibold">Cabang Aktif</label>
      <select onchange="location.href='/switch-branch/'+this.value" class="mt-1.5 w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white">
        @foreach($branches2 as $b)<option value="{{ $b->id }}" @selected($bid2==$b->id)>{{ $b->code }} - {{ $b->name }}</option>@endforeach
      </select>
    </div>
    <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto text-[15px] no-scrollbar">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('dashboard')?'bg-indigo-600 text-white':'hover:bg-slate-800' }}">📊 Dashboard</a>
      <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('pos.*')?'bg-indigo-600 text-white':'hover:bg-slate-800' }}">🛒 POS Kasir</a>
      <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('orders.*')?'bg-indigo-600 text-white':'hover:bg-slate-800' }}">📦 Orders</a>
      <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('customers.*')?'bg-indigo-600 text-white':'hover:bg-slate-800' }}">👥 Customers</a>
      <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('products.*')?'bg-indigo-600 text-white':'hover:bg-slate-800' }}">🧴 Produk</a>
      <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('categories.*')?'bg-indigo-600 text-white':'hover:bg-slate-800' }}">🏷️ Kategori</a>
      <a href="{{ route('cash.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('cash.*')?'bg-indigo-600 text-white':'hover:bg-slate-800' }}">💰 Kas</a>
      <a href="{{ route('shifts.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl {{ request()->routeIs('shifts.*')?'bg-indigo-600 text-white':'hover:bg-slate-800' }}">🕒 Shift</a>
      <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-slate-800">📑 Laporan</a>
      @if(auth()->user()->isAdmin())
      <div class="pt-3 mt-3 border-t border-slate-800 space-y-1">
        <a href="{{ route('branches.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-slate-800">🏢 Cabang</a>
        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-slate-800">👤 Users</a>
        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-slate-800">⚙️ Settings</a>
      </div>
      @endif
    </nav>
    <div class="p-4 border-t border-slate-800">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-slate-700 rounded-full grid place-items-center font-semibold">{{ substr(auth()->user()->name,0,1) }}</div>
        <div class="flex-1 min-w-0"><div class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</div><div class="text-xs text-slate-400 truncate">{{ auth()->user()->roles->pluck('name')->join(', ') }}</div></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-9 h-9 grid place-items-center rounded-xl bg-slate-800 text-slate-300">↗</button></form>
      </div>
    </div>
  </aside>

  {{-- Main --}}
  <div class="flex-1 lg:ml-64 min-w-0 flex flex-col">
    {{-- Mobile topbar --}}
    <header class="lg:hidden bg-white/95 backdrop-blur border-b border-slate-200 sticky top-0 z-20">
      <div class="flex items-center gap-2 px-3 py-2.5">
        <button onclick="openDrawer()" class="w-10 h-10 grid place-items-center rounded-xl bg-slate-900 text-white shrink-0" aria-label="Menu">☰</button>
        <div class="flex-1 min-w-0 flex items-center gap-2">
          <div class="w-8 h-8 bg-indigo-600 rounded-lg grid place-items-center text-white font-bold text-sm shrink-0">L</div>
          <div class="min-w-0"><div class="font-semibold text-sm leading-none truncate">{{ setting('app_name','Londry') }}</div><div class="text-[11px] text-slate-500 truncate">{{ current_branch()? current_branch()->code.' • '.current_branch()->name : 'Pilih cabang' }}</div></div>
        </div>
        <a href="{{ route('pos.index') }}" class="shrink-0 bg-indigo-600 active:bg-indigo-700 text-white px-3.5 py-2 rounded-xl text-sm font-semibold">POS</a>
      </div>
    </header>

    <main class="flex-1 p-3 sm:p-4 lg:p-6 pb-24 lg:pb-6 w-full max-w-full overflow-x-hidden">
      @if(session('success'))<div class="mb-3 sm:mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 sm:px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mb-3 sm:mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-3 sm:px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>@endif
      @if($errors->any())<div class="mb-3 sm:mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-3 sm:px-4 py-3 rounded-xl"><ul class="list-disc list-inside text-sm space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      @yield('content')
    </main>

    {{-- Mobile bottom nav --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-20 safe-pb">
      <div class="grid grid-cols-5 gap-1 px-1 py-1">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-2 rounded-xl {{ request()->routeIs('dashboard')?'text-indigo-600 bg-indigo-50':'text-slate-500' }}"><span class="text-[18px] leading-none">📊</span><span class="text-[10px] font-medium mt-1">Home</span></a>
        <a href="{{ route('pos.index') }}" class="flex flex-col items-center justify-center py-2 rounded-xl {{ request()->routeIs('pos.*')?'text-indigo-600 bg-indigo-50':'text-slate-500' }}"><span class="text-[18px] leading-none">🛒</span><span class="text-[10px] font-medium mt-1">POS</span></a>
        <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center py-2 rounded-xl {{ request()->routeIs('orders.*')?'text-indigo-600 bg-indigo-50':'text-slate-500' }}"><span class="text-[18px] leading-none">📦</span><span class="text-[10px] font-medium mt-1">Orders</span></a>
        <a href="{{ route('customers.index') }}" class="flex flex-col items-center justify-center py-2 rounded-xl {{ request()->routeIs('customers.*')?'text-indigo-600 bg-indigo-50':'text-slate-500' }}"><span class="text-[18px] leading-none">👥</span><span class="text-[10px] font-medium mt-1">Cust</span></a>
        <button onclick="openDrawer()" class="flex flex-col items-center justify-center py-2 rounded-xl text-slate-500"><span class="text-[18px] leading-none">☰</span><span class="text-[10px] font-medium mt-1">Menu</span></button>
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
