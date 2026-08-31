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
</div>
@endsection
