@extends('layouts.app')
@section('title','Edit Kategori')
@section('content')
<h1 class="text-xl font-bold mb-4">Edit {{ $category->name }}</h1>
<form method="POST" action="{{ route('categories.update',$category) }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-xl">@csrf @method('PUT')
<div><label class="text-sm">Nama *</label><input name="name" value="{{ $category->name }}" required class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div><label class="text-sm">Kode *</label><input name="code" value="{{ $category->code }}" required class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div><label class="text-sm">Deskripsi</label><textarea name="description" class="mt-1 w-full border rounded-xl px-3 py-2">{{ $category->description }}</textarea></div>
<div><label class="text-sm">Status</label><select name="status" class="mt-1 w-full border rounded-xl px-3 py-2"><option value="active" @selected($category->status=='active')>active</option><option value="inactive" @selected($category->status=='inactive')>inactive</option></select></div>
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Update</button>
</form>
@endsection
