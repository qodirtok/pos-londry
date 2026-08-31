@extends('layouts.app')
@section('title','Edit Merchant')
@section('content')
<div class="max-w-xl"><h1 class="text-xl font-bold mb-4">Edit Merchant</h1>
<form method="POST" action="{{ route('merchants.update',$merchant) }}" class="bg-white rounded-2xl border p-6 space-y-4">@csrf @method('PUT')
<div><label class="text-sm font-medium">Kode *</label><input name="code" value="{{ $merchant->code }}" required class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div>
<div><label class="text-sm font-medium">Nama Toko *</label><input name="name" value="{{ $merchant->name }}" required class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4"><div><label class="text-sm font-medium">Phone</label><input name="phone" value="{{ $merchant->phone }}" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div><div><label class="text-sm font-medium">Kota</label><input name="city" value="{{ $merchant->city }}" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div></div>
<div><label class="text-sm font-medium">Alamat</label><input name="address" value="{{ $merchant->address }}" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div>
<div><label class="text-sm font-medium">Email</label><input name="email" value="{{ $merchant->email }}" type="email" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"></div>
<div><label class="text-sm font-medium">Status</label><select name="status" class="mt-1 w-full border rounded-xl px-4 py-3 text-sm"><option value="active" @selected($merchant->status=='active')>active</option><option value="inactive" @selected($merchant->status=='inactive')>inactive</option></select></div>
<div class="flex gap-2 pt-2"><a href="{{ route('merchants.index') }}" class="flex-1 border rounded-xl px-4 py-3 text-sm text-center">Batal</a><button class="flex-1 bg-indigo-600 text-white rounded-xl px-4 py-3 text-sm font-semibold">Update</button></div>
</form></div>
@endsection
