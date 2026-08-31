@extends('layouts.guest')
@section('content')
<div class="w-full max-w-md">
  <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
    <div class="text-center mb-6">
      <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto">L</div>
      <h1 class="text-2xl font-bold mt-3">Masuk ke Londry POS</h1>
      <p class="text-sm text-slate-500">Laundry Point of Sale - Multi Cabang</p>
    </div>
    @if($errors->any())<div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>@endif
    <form method="POST" action="/login" class="space-y-4">@csrf
      <div><label class="text-sm font-medium">Username atau Email</label><input name="username_or_email" value="{{ old('username_or_email') }}" required class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="username atau email"></div>
      <div><label class="text-sm font-medium">Password</label><input type="password" name="password" required class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="••••••••"></div>
      <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="rounded"> Ingat saya</label>
      <button class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-3 rounded-xl">Masuk</button>
    </form>
  </div>

  <div class="mt-4 bg-amber-50 border border-amber-200 rounded-2xl p-4">
    <div class="flex items-center gap-2"><span class="bg-amber-900 text-amber-50 text-[10px] px-2 py-1 rounded-full font-bold">DEMO</span><span class="text-xs font-bold tracking-widest text-amber-800 uppercase">Akun Demo</span></div>
    <p class="text-[11px] text-amber-800/80 mt-2 leading-relaxed">Gunakan akun demo untuk mencoba — data dummy terisolasi, tidak mengganggu data produksi.</p>
    <div class="mt-3 space-y-1.5 text-sm">
      <div class="flex justify-between gap-2"><span class="text-amber-900/70">Demo Admin</span><span class="font-mono font-semibold text-amber-900">demo_admin / demo123</span></div>
      <div class="text-xs text-amber-800/60">demo.admin@londry.test</div>
      <div class="flex justify-between gap-2"><span class="text-amber-900/70">Demo Kasir</span><span class="font-mono font-semibold text-amber-900">demo_kasir / demo123</span></div>
    </div>
    <div class="mt-3 flex gap-2">
      <button type="button" onclick="fillLogin('demo_admin','demo123')" class="flex-1 bg-amber-900 hover:bg-black text-white rounded-xl px-3 py-2.5 text-xs font-semibold">Isi Demo Admin</button>
      <button type="button" onclick="fillLogin('demo_kasir','demo123')" class="flex-1 bg-white border border-amber-300 hover:bg-amber-100 text-amber-900 rounded-xl px-3 py-2.5 text-xs font-semibold">Isi Demo Kasir</button>
    </div>
  </div>
</div>

<script>
function fillLogin(u,p){
  const a=document.querySelector('input[name="username_or_email"]');
  const b=document.querySelector('input[name="password"]');
  if(a) a.value=u;
  if(b) b.value=p;
  if(a) a.focus();
}
</script>
@endsection
