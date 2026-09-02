@extends('layouts.app')
@section('title','Produk')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
  <div>
    <h1 class="text-xl font-bold">Produk & Layanan</h1>
    <p class="text-xs text-slate-500 mt-0.5">Total: {{ $products->total() }} item</p>
  </div>
  <a href="{{ route('products.create') }}" class="bg-indigo-600 active:bg-indigo-700 text-white px-4 py-3 sm:py-2 rounded-xl text-sm font-semibold text-center inline-flex items-center gap-1.5">
    <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1rem;height:1rem"><path d="M12 5v14M5 12h14"/></svg>
    Produk
  </a>
</div>

<form class="bg-white rounded-2xl border p-3 mb-4 flex flex-col sm:flex-row gap-2" method="GET" action="{{ route('products.index') }}">
  <div class="flex-1 relative">
    <input name="search" value="{{ request('search') }}" placeholder="Cari nama / SKU / barcode" class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
    <svg class="pos-flat-icon" viewBox="0 0 24 24" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);width:1.1rem;height:1.1rem;color:#94a3b8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
  </div>
  <div class="flex gap-2">
    <select name="category_id" class="flex-1 sm:w-40 border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white">
      <option value="">Semua kategori</option>
      @foreach(\App\Models\Category::orderBy('name')->get() as $c)
        <option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>
      @endforeach
    </select>
    <select name="type" class="flex-1 sm:w-32 border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white">
      <option value="">Semua tipe</option>
      <option value="product" @selected(request('type')=='product')>Produk</option>
      <option value="service" @selected(request('type')=='service')>Jasa</option>
    </select>
    <select name="status" class="flex-1 sm:w-32 border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white">
      <option value="">Semua status</option>
      <option value="active" @selected(request('status')=='active')>Aktif</option>
      <option value="inactive" @selected(request('status')=='inactive')>Nonaktif</option>
    </select>
    <button class="bg-slate-900 text-white px-5 py-3 rounded-xl text-sm font-medium inline-flex items-center gap-1">
      <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1rem;height:1rem"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      Cari
    </button>
  </div>
</form>

@if($products->count() === 0)
  <div class="bg-white border border-dashed rounded-2xl p-10 text-center text-sm text-slate-500">
    <svg class="pos-flat-icon mx-auto mb-2" viewBox="0 0 24 24" style="width:2.5rem;height:2.5rem;color:#cbd5e1"><path d="M20 7l-8-4-8 4m16 0v10l-8 4-8-4V7m16 0L12 11M4 7l8 4m0 0v10"/></svg>
    <p class="font-semibold text-slate-600">Tidak ada produk ditemukan</p>
    <p class="text-xs mt-1">Coba ubah filter atau tambah produk baru</p>
  </div>
@else
<div class="grid grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4">
  @foreach($products as $p)
    @php
      $totalStock = \App\Models\ProductStock::where('product_id',$p->id)->sum('quantity');
      $isLow = $p->type==='product' && $totalStock <= 5;
      $isInactive = ($p->status ?? 'active') !== 'active';
    @endphp
    <div class="bg-white rounded-2xl border p-3 sm:p-4 flex flex-col gap-2 {{ $isInactive ? 'opacity-60' : '' }}">
      <div class="flex justify-between items-start gap-1">
        <span class="text-[11px] font-mono bg-slate-100 px-2 py-1 rounded-full truncate max-w-[60%]" title="{{ $p->sku }}">{{ $p->sku }}</span>
        <span class="text-[11px] px-2 py-1 rounded-full shrink-0 font-semibold {{ $p->type=='service'?'bg-indigo-100 text-indigo-700':'bg-emerald-100 text-emerald-700' }}">{{ $p->type=='service' ? 'Jasa' : 'Produk' }}</span>
      </div>
      <div class="font-semibold text-sm leading-snug line-clamp-2 min-h-[2.5rem]">{{ $p->name }}</div>
      <div class="text-xs text-slate-500 truncate">{{ $p->category->name ?? 'Tanpa kategori' }}</div>
      <div class="flex items-end justify-between gap-2 mt-1">
        <div class="font-bold text-indigo-600 text-base">{{ money($p->price) }}<span class="text-[11px] text-slate-400 font-normal">/{{ $p->unit }}</span></div>
        @if($p->type==='product')
          <div class="text-right">
            <div class="text-[10px] text-slate-400 uppercase tracking-wider">Stok</div>
            <div class="text-xs font-semibold {{ $isLow ? 'text-rose-600' : 'text-slate-700' }}">{{ number_format($totalStock,0,',','.') }} {{ $p->unit }}</div>
          </div>
        @endif
      </div>
      @if($isInactive)
        <div class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded-md text-center font-semibold uppercase tracking-wider">Nonaktif</div>
      @endif
      <div class="flex gap-2 mt-2 pt-2 border-t border-slate-100">
        <a href="{{ route('products.edit',$p) }}" class="flex-1 text-center text-xs bg-slate-900 hover:bg-slate-800 text-white px-3 py-2.5 rounded-xl font-medium inline-flex items-center justify-center gap-1">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.85rem;height:.85rem"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.25 4.25 0 01-1.897 1.21l-2.25.5a.75.75 0 01-.906-.906 4.25 4.25 0 011.21-1.897L16.862 4.487zm0 0L19.5 7.125"/></svg>
          Edit
        </a>
        @if($isInactive)
          <form method="POST" action="{{ route('products.update',$p) }}" class="flex-1" onsubmit="return confirm('Aktifkan {{ $p->name }}?')">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="active">
            <input type="hidden" name="name" value="{{ $p->name }}">
            <input type="hidden" name="category_id" value="{{ $p->category_id }}">
            <input type="hidden" name="type" value="{{ $p->type }}">
            <input type="hidden" name="price" value="{{ $p->price }}">
            <input type="hidden" name="unit" value="{{ $p->unit }}">
            <input type="hidden" name="sku" value="{{ $p->sku }}">
            <button class="w-full text-xs border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-3 py-2.5 rounded-xl font-medium">Aktifkan</button>
          </form>
        @else
          <form method="POST" action="{{ route('products.destroy',$p) }}" onsubmit="return confirm('{{ $p->type=='product' ? 'Nonaktifkan' : 'Hapus' }} {{ $p->name }}?')" class="flex-1">
            @csrf @method('DELETE')
            <button class="w-full text-xs border border-slate-200 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 px-3 py-2.5 rounded-xl font-medium inline-flex items-center justify-center gap-1">
              <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.85rem;height:.85rem"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
              {{ $p->type=='product' ? 'Nonaktifkan' : 'Hapus' }}
            </button>
          </form>
        @endif
      </div>
    </div>
  @endforeach
</div>
<div class="mt-4">{{ $products->withQueryString()->links() }}</div>
@endif
@endsection
