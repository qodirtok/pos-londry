@extends('layouts.app')
@section('title','Tambah Kas')
@section('content')
<h1 class="text-xl font-bold mb-4">Tambah Kas</h1>
<form method="POST" action="{{ route('cash.store') }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-xl">@csrf
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">Tipe *</label><select name="type" required class="mt-1 w-full border rounded-xl px-3 py-2"><option value="income">income</option><option value="expense">expense</option></select></div><div><label class="text-sm">Kategori</label><select name="category" class="mt-1 w-full border rounded-xl px-3 py-2"><option value="">- Pilih -</option>@foreach($categories as $c)<option value="{{ $c->name }}">{{ $c->name }} ({{ $c->type }})</option>@endforeach</select></div></div>
<div><label class="text-sm">Nominal *</label><input name="amount" type="number" required class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div><label class="text-sm">Tanggal</label><input name="transaction_date" type="date" value="{{ date('Y-m-d') }}" class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div><label class="text-sm">Keterangan</label><textarea name="description" class="mt-1 w-full border rounded-xl px-3 py-2"></textarea></div>
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Simpan</button>
</form>
@endsection
