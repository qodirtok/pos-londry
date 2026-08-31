@extends('layouts.app')
@section('title','Tambah Produk')
@section('content')
<h1 class="text-xl font-bold mb-4">Tambah Produk / Layanan</h1>
<form method="POST" action="{{ route('products.store') }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-2xl">@csrf
<div><label class="text-sm">Nama *</label><input name="name" required class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">SKU (kosongkan = auto)</label><input name="sku" placeholder="PRD-000005" class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Barcode</label><input name="barcode" class="mt-1 w-full border rounded-xl px-3 py-2"></div></div>
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">Kategori *</label><select name="category_id" required class="mt-1 w-full border rounded-xl px-3 py-2">@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div><div><label class="text-sm">Tipe *</label><select name="type" required class="mt-1 w-full border rounded-xl px-3 py-2"><option value="product">Product (barang, stok)</option><option value="service">Service (laundry, kg/pcs)</option></select></div></div>
<div class="grid grid-cols-3 gap-4"><div><label class="text-sm">Harga *</label><input name="price" type="number" required class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">HPP</label><input name="cost" type="number" value="0" class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Unit *</label><select name="unit" class="mt-1 w-full border rounded-xl px-3 py-2"><option value="pcs">pcs</option><option value="kg">kg</option><option value="meter">meter</option><option value="liter">liter</option></select></div></div>
<div><label class="text-sm">Deskripsi</label><textarea name="description" class="mt-1 w-full border rounded-xl px-3 py-2"></textarea></div>
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Simpan</button>
</form>
@endsection
