@extends('layouts.app')
@section('title','Produk')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4"><h1 class="text-xl font-bold">Produk & Layanan</h1><a href="{{ route('products.create') }}" class="bg-indigo-600 active:bg-indigo-700 text-white px-4 py-3 sm:py-2 rounded-xl text-sm font-semibold text-center">+ Produk</a></div>
<form class="bg-white rounded-2xl border p-3 mb-4 flex flex-col sm:flex-row gap-2">
  <input name="search" value="{{ request('search') }}" placeholder="Cari nama / SKU / barcode" class="flex-1 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
  <div class="flex gap-2">
    <select name="type" class="flex-1 sm:w-40 border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white"><option value="">Semua tipe</option><option value="product" @selected(request('type')=='product')>Product</option><option value="service" @selected(request('type')=='service')>Service</option></select>
    <button class="bg-slate-900 text-white px-5 py-3 rounded-xl text-sm font-medium">Cari</button>
  </div>
</form>
<div class="grid grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4">
@foreach($products as $p)
<div class="bg-white rounded-2xl border p-3 sm:p-4 flex flex-col">
  <div class="flex justify-between items-start gap-1"><span class="text-[11px] font-mono bg-slate-100 px-2 py-1 rounded-full truncate">{{ $p->sku }} • {{ $p->unit }}</span><span class="text-[11px] px-2 py-1 rounded-full shrink-0 {{ $p->type=='service'?'bg-indigo-100 text-indigo-700':'bg-emerald-100 text-emerald-700' }}">{{ $p->type }}</span></div>
  <div class="font-semibold text-sm mt-2 line-clamp-2">{{ $p->name }}</div><div class="text-xs text-slate-500 truncate">{{ $p->category->name }}</div>
  <div class="font-bold text-indigo-600 mt-2 text-sm">{{ money($p->price) }}</div>
  <div class="flex gap-2 mt-3"><a href="{{ route('products.edit',$p) }}" class="flex-1 text-center text-xs bg-slate-900 text-white px-3 py-2.5 rounded-xl font-medium">Edit</a>
  <form method="POST" action="{{ route('products.destroy',$p) }}" onsubmit="return confirm('Hapus {{ $p->name }}?')" class="flex-1"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button class="w-full text-xs border border-slate-200 px-3 py-2.5 rounded-xl">Hapus</button></form>
</div>
@endforeach
</div>
<div class="mt-4">{{ $products->withQueryString()->links() }}</div>
@endsection
