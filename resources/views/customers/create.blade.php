@extends('layouts.app')
@section('title','Tambah Customer')
@section('content')
<h1 class="text-xl font-bold mb-4">Tambah Customer</h1>
<form method="POST" action="{{ route('customers.store') }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-xl">@csrf
<div><label class="text-sm">Nama *</label><input name="name" required class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">Phone</label><input name="phone" class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Email</label><input name="email" class="mt-1 w-full border rounded-xl px-3 py-2"></div></div>
<div><label class="text-sm">Alamat</label><textarea name="address" class="mt-1 w-full border rounded-xl px-3 py-2"></textarea></div>
<div><label class="text-sm">Catatan</label><textarea name="notes" class="mt-1 w-full border rounded-xl px-3 py-2"></textarea></div>
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Simpan</button>
</form>
@endsection
