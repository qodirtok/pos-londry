@extends('layouts.app')
@section('title','Tambah User')
@section('content')
<h1 class="text-xl font-bold mb-4">Tambah User</h1>
<form method="POST" action="{{ route('users.store') }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-2xl">@csrf
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">Nama *</label><input name="name" required class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Username *</label><input name="username" required class="mt-1 w-full border rounded-xl px-3 py-2"></div></div>
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">Email *</label><input name="email" type="email" required class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Phone</label><input name="phone" class="mt-1 w-full border rounded-xl px-3 py-2"></div></div>
<div class="grid grid-cols-2 gap-4"><div><label class="text-sm">Password *</label><input name="password" type="password" required class="mt-1 w-full border rounded-xl px-3 py-2"></div><div><label class="text-sm">Cabang</label><select name="branch_id" class="mt-1 w-full border rounded-xl px-3 py-2"><option value="">- Pilih -</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->code }} - {{ $b->name }}</option>@endforeach</select></div></div>
<div><label class="text-sm">Roles <span class="text-xs text-slate-400">— Manager = sub-admin toko (kelola kasir & produk)</span></label><div class="flex gap-4 mt-1 flex-wrap">@foreach($roles as $r)<label class="flex items-center gap-1 text-sm"><input type="checkbox" name="roles[]" value="{{ $r->id }}"> {{ $r->display_name ?? $r->name }}@if($r->name=='Manager')<span class="text-xs text-slate-500">(sub admin)</span>@endif</label>@endforeach</div></div>
<div><label class="text-sm">Status</label><select name="status" class="mt-1 w-full border rounded-xl px-3 py-2"><option value="active">active</option><option value="inactive">inactive</option></select></div>
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Simpan</button>
</form>
@endsection
