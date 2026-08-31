@extends('layouts.app')
@section('title','Customer Detail')
@section('content')
<h1 class="text-xl font-bold mb-2">{{ $customer->name }} <span class="text-sm font-normal text-slate-500">{{ $customer->code }}</span></h1>
<p class="text-sm text-slate-500 mb-4">{{ $customer->phone }} • {{ $customer->address }}</p>
<div class="bg-white rounded-2xl border p-4">
<h3 class="font-semibold mb-3">Riwayat Transaksi (10 terakhir)</h3>
<table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="text-left p-2">No</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead><tbody>@forelse($customer->orders as $o)<tr class="border-t"><td class="p-2 font-mono text-xs">{{ $o->order_number }}</td><td>{{ $o->order_date }}</td><td>{{ money($o->total) }}</td><td>{{ $o->order_status }}</td></tr>@empty<tr><td colspan="4" class="p-4 text-center text-slate-400">Belum ada transaksi</td></tr>@endforelse</tbody></table>
</div>
@endsection
