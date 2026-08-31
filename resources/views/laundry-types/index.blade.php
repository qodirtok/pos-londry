@extends('layouts.app')
@section('title','Jenis Laundry')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
  <h1 class="text-xl font-bold">Jenis Rincian Laundry</h1>
  <span class="text-xs text-slate-500">Tersimpan — otomatis muncul di POS (dinamis)</span>
</div>
<div class="bg-white rounded-2xl border p-4 mb-4">
  <form method="POST" action="{{ route('laundry-types.store') }}" class="flex flex-col sm:flex-row gap-2">
    @csrf
    <input name="name" required placeholder="Nama jenis, contoh: Gorden" class="flex-1 border border-slate-200 rounded-xl px-3 py-3 text-sm">
    <input name="icon" placeholder="Icon (opsional) contoh: 🧹" class="w-full sm:w-28 border border-slate-200 rounded-xl px-3 py-3 text-sm">
    <button class="bg-indigo-600 text-white px-5 py-3 rounded-xl text-sm font-semibold">+ Tambah</button>
  </form>
  <p class="text-[11px] text-slate-500 mt-2">Jenis yang ditambah akan langsung muncul di POS & bisa diisi pcs per order. Hapus jenis yang tidak dipakai.</p>
</div>
<div class="bg-white rounded-2xl border overflow-hidden">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 p-3 sm:p-4">
    @foreach($types as $t)
    <div class="border border-slate-200 rounded-xl p-3 sm:p-3.5 flex items-center justify-between gap-2 bg-slate-50 min-w-0">
      <div class="min-w-0"><div class="text-sm font-semibold truncate">{{ $t->icon ?? '📦' }} {{ $t->name }}</div><div class="text-xs font-mono text-slate-500 truncate">{{ $t->code }}</div></div>
      <form method="POST" action="{{ route('laundry-types.destroy',$t->id) }}" onsubmit="return confirm('Hapus {{ $t->name }}?')">@csrf @method('DELETE')<button class="w-8 h-8 grid place-items-center rounded-lg bg-white border hover:bg-rose-50 text-slate-500 hover:text-rose-600">×</button></form>
    </div>
    @endforeach
  </div>
</div>
@endsection
