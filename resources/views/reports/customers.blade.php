@extends('layouts.app')
@section('title','Laporan Customer')
@section('content')
<h1 class="text-xl font-bold mb-4">Laporan Customer - Top Spender</h1>
<div class="bg-white rounded-2xl border overflow-hidden"><table class="w-full text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="text-left p-3">Customer</th><th>Transaksi</th><th class="text-right">Total Belanja</th></tr></thead><tbody>@foreach($data as $c)<tr class="border-t"><td class="p-3">{{ $c->name }}<div class="text-xs text-slate-500">{{ $c->phone }}</div></td><td class="text-center">{{ $c->orders_count }}</td><td class="text-right">{{ money($c->orders_sum_total ?? 0) }}</td></tr>@endforeach</tbody></table></div>
@endsection
