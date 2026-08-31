@extends('layouts.app')
@section('title','Customers')
@section('content')
<div class="flex justify-between items-center mb-4"><h1 class="text-xl font-bold">Customers</h1><a href="{{ route('customers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm">+ Customer</a></div>
<form class="mb-4 flex gap-2"><input name="search" value="{{ request('search') }}" placeholder="Cari nama / HP / kode" class="flex-1 border rounded-xl px-4 py-2"><button class="bg-slate-900 text-white px-4 py-2 rounded-xl">Cari</button></form>
<div class="bg-white rounded-2xl border overflow-hidden"><table class="w-full text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="text-left p-3">Kode</th><th class="text-left">Nama</th><th>HP</th><th>Cabang</th><th></th></tr></thead><tbody>@foreach($customers as $c)<tr class="border-t"><td class="p-3 font-mono text-xs">{{ $c->code }}</td><td class="font-medium">{{ $c->name }}</td><td>{{ $c->phone }}</td><td class="text-xs">{{ $c->branch->name ?? '-' }}</td><td class="text-right p-3"><a href="{{ route('customers.edit',$c) }}" class="text-indigo-600 text-xs mr-2">Edit</a><a href="{{ route('customers.show',$c) }}" class="text-slate-500 text-xs">Detail</a></td></tr>@endforeach</tbody></table><div class="p-3">{{ $customers->withQueryString()->links() }}</div></div>
@endsection
