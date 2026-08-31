@extends('layouts.app')
@section('title','Edit Cabang')
@section('content')
<h1 class="text-xl font-bold mb-4">Edit Cabang {{ $branch->code }}</h1>
<form method="POST" action="{{ route('branches.update',$branch) }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-2xl">@csrf @method('PUT')
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm font-medium">Kode *</label><input name="code" value="{{ $branch->code }}" required class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm font-medium">Nama *</label><input name="name" value="{{ $branch->name }}" required class="mt-1 w-full border rounded-xl px-3 py-2"></div></div>
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">Phone</label><input name="phone" value="{{ $branch->phone }}" class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Email</label><input name="email" value="{{ $branch->email }}" class="mt-1 w-full border rounded-xl px-3 py-2"></div></div>
<div><label class="text-sm">Alamat</label><textarea name="address" class="mt-1 w-full border rounded-xl px-3 py-2">{{ $branch->address }}</textarea></div>
<div class="grid grid-cols-3 gap-4"><div><label class="text-sm">Kota</label><input name="city" value="{{ $branch->city }}" class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Provinsi</label><input name="province" value="{{ $branch->province }}" class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Status</label><select name="status" class="mt-1 w-full border rounded-xl px-3 py-2"><option value="active" @selected($branch->status=='active')>active</option><option value="inactive" @selected($branch->status=='inactive')>inactive</option></select></div></div>
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Update</button>
</form>
@endsection
