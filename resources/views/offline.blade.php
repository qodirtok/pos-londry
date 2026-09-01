@extends('layouts.guest')
@section('content')
<div class="w-full max-w-md">
  <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 text-center">
    <div class="w-14 h-14 bg-amber-400 rounded-2xl flex items-center justify-center text-white mx-auto">{{ icon('cash', ['size':28, 'color':'#fff']) }}</div>
    <h1 class="text-xl font-bold mt-4">Kamu sedang offline</h1>
    <p class="text-sm text-slate-500 mt-2 leading-relaxed">Tidak ada koneksi internet. Periksa jaringan, lalu coba lagi. Halaman yang dibuka sebelumnya mungkin masih tersedia dari cache.</p>
    <div class="mt-6 flex gap-2 justify-center">
      <button onclick="location.reload()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-3 rounded-xl text-sm">Coba lagi</button>
      <a href="/dashboard" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-800 font-semibold px-5 py-3 rounded-xl text-sm">Ke Dashboard</a>
    </div>
    <p class="text-xs text-slate-400 mt-4">Londry POS — akan otomatis sinkron saat online kembali.</p>
  </div>
</div>
@endsection
