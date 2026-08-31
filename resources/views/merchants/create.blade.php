@extends('layouts.app')
@section('title','Tambah Merchant')
@section('content')
<div class="max-w-xl"><h1 class="text-xl font-bold mb-4">Tambah Merchant / Toko</h1><p class="text-sm text-slate-500 mb-4">Tiap merchant bisa punya 1 Admin, banyak Manager (sub-admin), dan Kasir. Cabang & data terisolasi per merchant.</p>
<form method="POST" action="{{ route('merchants.store') }}" class="bg-white rounded-2xl border p-6 space-y-4">@csrf
<div><label class="text-sm font-medium">Kode *</label><input name="code" required placeholder="TOKO-002" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
<div><label class="text-sm font-medium">Nama Toko *</label><input name="name" required placeholder="Londry Cabang Baru" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4"><div><label class="text-sm font-medium">Phone</label><input name="phone" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div><div><label class="text-sm font-medium">Kota</label><input name="city" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div></div>
<div><label class="text-sm font-medium">Alamat</label><input name="address" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div>
<div><label class="text-sm font-medium">Email</label><input name="email" type="email" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div>
<div><label class="text-sm font-medium">Status</label><select name="status" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"><option value="active">active</option><option value="inactive">inactive</option></select></div>
<div class="flex gap-2 pt-2"><a href="{{ route('merchants.index') }}" class="flex-1 border rounded-xl px-4 py-3 text-sm text-center">Batal</a><button class="flex-1 bg-indigo-600 text-white rounded-xl px-4 py-3 text-sm font-semibold">Simpan</button></div>
</form></div>
@endsection
