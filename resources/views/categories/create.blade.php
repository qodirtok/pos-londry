@extends('layouts.app')
@section('title','Tambah Kategori')
@section('content')
<h1 class="text-xl font-bold mb-4">Tambah Kategori</h1>
<form method="POST" action="{{ route('categories.store') }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-xl">@csrf
<div><label class="text-sm">Nama *</label><input name="name" required class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div><label class="text-sm">Kode *</label><input name="code" required placeholder="Cth: LNDRY" class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div><label class="text-sm">Deskripsi</label><textarea name="description" class="mt-1 w-full border rounded-xl px-3 py-2"></textarea></div>
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Simpan</button>
</form>
@endsection
