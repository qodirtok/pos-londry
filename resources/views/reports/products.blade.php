@extends('layouts.app')
@section('title','Laporan Produk')
@section('content')
<h1 class="text-xl font-bold mb-4">Laporan Produk</h1>
<form class="bg-white rounded-2xl border p-4 mb-4 flex gap-2"><input name="from" type="date" value="{{ $from }}" class="border rounded-xl px-3 py-2"><input name="to" type="date" value="{{ request('to', date('Y-m-d')) }}" class="border rounded-xl px-3 py-2"><button class="bg-slate-900 text-white px-4 py-2 rounded-xl">Filter</button></form>
<div class="bg-white rounded-2xl border p-4"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="text-left p-2">Produk</th><th>Qty</th><th class="text-right">Revenue</th></tr></thead><tbody>@foreach($data as $d)<tr class="border-t"><td class="p-2">{{ $d->name }}</td><td class="text-center">{{ $d->qty }}</td><td class="text-right">{{ money($d->revenue) }}</td></tr>@endforeach</tbody></table></div>
@endsection
