@extends('layouts.app')
@section('title','Edit Customer')
@section('content')
<h1 class="text-xl font-bold mb-4">Edit {{ $customer->name }}</h1>
<form method="POST" action="{{ route('customers.update',$customer) }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-xl">@csrf @method('PUT')
<div><label class="text-sm">Nama *</label><input name="name" value="{{ $customer->name }}" required class="mt-1 w-full border rounded-xl px-3 py-2"></div>
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">Phone</label><input name="phone" value="{{ $customer->phone }}" class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Email</label><input name="email" value="{{ $customer->email }}" class="mt-1 w-full border rounded-xl px-3 py-2"></div></div>
<div><label class="text-sm">Alamat</label><textarea name="address" class="mt-1 w-full border rounded-xl px-3 py-2">{{ $customer->address }}</textarea></div>
<div><label class="text-sm">Catatan</label><textarea name="notes" class="mt-1 w-full border rounded-xl px-3 py-2">{{ $customer->notes }}</textarea></div>
<div><label class="text-sm">Status</label><select name="status" class="mt-1 w-full border rounded-xl px-3 py-2"><option value="active" @selected($customer->status=='active')>active</option><option value="inactive" @selected($customer->status=='inactive')>inactive</option></select></div>
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Update</button>
</form>
@endsection
