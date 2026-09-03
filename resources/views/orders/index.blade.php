@extends('layouts.app')
@section('title','Orders')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4"><h1 class="text-xl font-bold">Orders</h1><a href="{{ route('pos.index') }}" class="bg-indigo-600 active:bg-indigo-700 text-white px-4 py-3 sm:py-2 rounded-xl text-sm font-semibold text-center">+ POS Baru</a></div>
<form class="bg-white rounded-2xl border p-3 sm:p-4 mb-4">
  <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="No. order / customer" class="sm:col-span-5 border border-slate-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
    <select name="status" class="sm:col-span-3 border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white"><option value="">Semua status</option>@foreach(['received','ready','picked_up','complete','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select>
    <div class="sm:col-span-4 flex gap-2">
      <input name="from" type="date" value="{{ request('from') }}" class="flex-1 border border-slate-200 rounded-xl px-2 py-3 text-xs sm:text-sm"><input name="to" type="date" value="{{ request('to') }}" class="flex-1 border border-slate-200 rounded-xl px-2 py-3 text-xs sm:text-sm">
      <button class="bg-slate-900 text-white px-4 py-3 rounded-xl text-sm font-medium shrink-0">Filter</button>
    </div>
  </div>
</form>
{{-- Desktop table --}}
<div class="hidden sm:block bg-white rounded-2xl border overflow-hidden overflow-x-auto">
  <table class="w-full text-sm min-w-[640px]"><thead class="bg-slate-50 text-slate-500"><tr><th class="text-left p-3">Order</th><th class="text-left">Customer</th><th>Tanggal</th><th class="text-right">Total</th><th class="text-center">Bayar</th><th class="text-center">Status</th><th></th></tr></thead><tbody>@forelse($orders as $o)<tr class="border-t hover:bg-slate-50"><td class="p-3"><div class="font-mono text-xs font-semibold">{{ $o->order_number }}</div><div class="text-xs text-slate-500">{{ $o->branch->code ?? '' }}</div></td><td class="text-sm">{{ $o->customer->name ?? 'Walk-in' }}<div class="text-xs text-slate-500">{{ $o->customer->phone ?? '' }}</div></td><td class="text-xs whitespace-nowrap">{{ $o->order_date->format('d/m/Y') }}</td><td class="text-right font-medium">{{ money($o->total) }}</td><td class="text-center"><span class="px-2 py-1 rounded-full text-xs {{ $o->payment_status=='paid'?'bg-emerald-100 text-emerald-700':($o->payment_status=='partial'?'bg-amber-100 text-amber-700':'bg-rose-100 text-rose-700') }}">{{ $o->payment_status }}</span></td><td class="text-center"><span class="px-2 py-1 rounded-full text-xs bg-slate-100 capitalize">{{ str_replace('_',' ',$o->order_status) }}</span></td><td class="text-right p-3"><a href="{{ route('orders.show',$o) }}" class="text-indigo-600 text-xs font-medium">Detail</a></td></tr>@empty<tr><td colspan="7" class="p-8 text-center text-slate-400">Belum ada order</td></tr>@endforelse</tbody></table>
  <div class="p-3 border-t">{{ $orders->withQueryString()->links() }}</div>
</div>
{{-- Mobile cards --}}
<div class="sm:hidden space-y-2">
  @forelse($orders as $o)
  <a href="{{ route('orders.show',$o) }}" class="block bg-white rounded-2xl border p-3 active:bg-slate-50">
    <div class="flex justify-between gap-2"><span class="font-mono text-xs font-bold truncate">{{ $o->order_number }}</span><span class="text-xs text-slate-500 shrink-0">{{ $o->order_date->format('d/m/Y') }}</span></div>
    <div class="text-sm font-medium truncate mt-1">{{ $o->customer->name ?? 'Walk-in' }} <span class="text-xs text-slate-500">• {{ $o->branch->code ?? '' }}</span></div>
    <div class="flex items-center justify-between mt-2"><span class="font-bold text-sm">{{ money($o->total) }}</span><span class="flex gap-1.5"><span class="px-2 py-1 rounded-full text-[11px] {{ $o->payment_status=='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700' }}">{{ $o->payment_status }}</span><span class="px-2 py-1 rounded-full text-[11px] bg-slate-100 capitalize">{{ str_replace('_',' ',$o->order_status) }}</span></span></div>
  </a>
  @empty <div class="bg-white rounded-2xl border p-8 text-center text-slate-400 text-sm">Belum ada order</div> @endforelse
  <div class="pt-2">{{ $orders->withQueryString()->links() }}</div>
</div>
@endsection
