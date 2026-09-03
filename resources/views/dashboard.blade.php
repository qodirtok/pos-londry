@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 sm:mb-6">
  <h1 class="text-xl sm:text-2xl font-bold">Dashboard</h1>
  <span class="text-xs sm:text-sm text-slate-500">{{ now()->format('d M Y') }} • {{ current_branch()? current_branch()->name : 'Semua Cabang' }}</span>
</div>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
  <div class="bg-white rounded-2xl p-4 sm:p-5 border"><div class="text-xs sm:text-sm text-slate-500">Penjualan Hari Ini</div><div class="text-lg sm:text-2xl font-bold mt-1">{{ money($stats['today_sales']) }}</div><div class="text-[11px] sm:text-xs text-slate-400 mt-1">{{ $stats['today_orders'] }} transaksi</div></div>
  <div class="bg-white rounded-2xl p-4 sm:p-5 border"><div class="text-xs sm:text-sm text-slate-500">Laundry Pending</div><div class="text-lg sm:text-2xl font-bold mt-1">{{ $stats['pending_laundry'] }}</div><div class="text-[11px] sm:text-xs text-amber-600 mt-1">{{ $stats['ready_laundry'] }} siap diambil</div></div>
  <div class="bg-white rounded-2xl p-4 sm:p-5 border"><div class="text-xs sm:text-sm text-slate-500">Kas Hari Ini</div><div class="text-xs sm:text-sm mt-2">Masuk: <b class="text-emerald-600">{{ money($stats['cash_income']) }}</b></div><div class="text-xs sm:text-sm">Keluar: <b class="text-rose-600">{{ money($stats['cash_expense']) }}</b></div></div>
  <div class="bg-white rounded-2xl p-4 sm:p-5 border"><div class="text-xs sm:text-sm text-slate-500">Piutang</div><div class="text-lg sm:text-2xl font-bold mt-1 text-amber-600 break-all">{{ money($stats['outstanding']) }}</div><div class="text-[11px] sm:text-xs text-slate-400">Belum lunas</div></div>
</div>
<div class="grid lg:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
  <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-5 border">
    <h3 class="font-semibold text-sm sm:text-base mb-3">Penjualan 7 Hari</h3>
    <div class="space-y-2.5">
      @forelse($sales7 as $d)
        <div class="flex items-center gap-2 sm:gap-3">
          <span class="text-[11px] sm:text-xs w-16 sm:w-24 shrink-0">{{ \Carbon\Carbon::parse($d->order_date)->format('d M') }}</span>
          <div class="flex-1 bg-slate-100 rounded-full h-2.5 sm:h-3 overflow-hidden"><div class="bg-indigo-600 h-2.5 sm:h-3 rounded-full" style="width: {{ $sales7->max('total')>0 ? ($d->total/$sales7->max('total')*100) : 0 }}%"></div></div>
          <span class="text-[11px] sm:text-xs font-medium w-20 sm:w-28 text-right shrink-0">{{ money($d->total) }}</span>
        </div>
      @empty <p class="text-sm text-slate-400 py-4 text-center">Belum ada data</p> @endforelse
    </div>
  </div>
  <div class="bg-white rounded-2xl p-4 sm:p-5 border">
    <h3 class="font-semibold text-sm sm:text-base mb-3">Status Laundry</h3>
    @forelse($byStatus as $st=>$cnt)<div class="flex justify-between py-2 border-b last:border-0 text-sm"><span class="capitalize text-slate-600">{{ str_replace('_',' ',$st) }}</span><span class="font-semibold">{{ $cnt }}</span></div>@empty <p class="text-sm text-slate-400">Belum ada order</p> @endforelse
    <a href="{{ route('orders.index') }}" class="mt-4 block text-center text-sm text-indigo-600 hover:underline py-2">Lihat semua order →</a>
  </div>
</div>
<div class="bg-white rounded-2xl border overflow-hidden mb-4 sm:mb-6">
  <div class="p-4 sm:p-5 border-b flex items-center justify-between">
    <h3 class="font-semibold text-sm sm:text-base">🕒 Antrian Terlama (Status: Received)</h3>
    <a href="{{ route('queue.index') }}" class="text-xs text-indigo-600">Lihat semua antrian</a>
  </div>
  @forelse($queueList as $i => $o)
  <a href="{{ route('orders.show',$o) }}" class="flex items-center gap-3 p-3 sm:p-4 border-b last:border-0 hover:bg-slate-50 active:bg-slate-100">
    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 grid place-items-center text-xs font-bold shrink-0">#{{ $i+1 }}</div>
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-2">
        <span class="font-mono text-xs font-semibold truncate">{{ $o->order_number }}</span>
        @if(!empty($o->laundry_details))
          <span class="bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded text-[10px] font-semibold border border-amber-100">Laundry</span>
        @else
          <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[10px] font-semibold">Produk</span>
        @endif
      </div>
      <div class="text-sm truncate">{{ $o->customer->name ?? 'Walk-in' }}</div>
      <div class="text-xs text-slate-500">{{ $o->order_date->format('d/m/Y H:i') }}</div>
    </div>
    <div class="text-right shrink-0">
      <div class="font-semibold text-sm">{{ money($o->total) }}</div>
    </div>
  </a>
  @empty
    <div class="p-8 text-center text-sm text-slate-400">Tidak ada antrian. Semua order sudah diproses. ✅</div>
  @endforelse
</div>
<div class="bg-white rounded-2xl border overflow-hidden">
  <div class="p-4 sm:p-5 border-b flex items-center justify-between"><h3 class="font-semibold text-sm sm:text-base">Transaksi Terbaru</h3><a href="{{ route('orders.index') }}" class="text-xs text-indigo-600">Lihat semua</a></div>
  {{-- Desktop table --}}
  <div class="hidden sm:block overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-slate-500 bg-slate-50/50"><tr><th class="text-left py-2.5 px-4">No. Order</th><th class="text-left">Customer</th><th class="text-right">Total</th><th class="text-center">Status</th><th class="text-center">Bayar</th></tr></thead>
      <tbody>@forelse($recent as $o)<tr class="border-t hover:bg-slate-50"><td class="py-2.5 px-4 font-mono text-xs">{{ $o->order_number }}</td><td class="text-sm">{{ $o->customer->name ?? 'Walk-in' }}</td><td class="text-right font-medium">{{ money($o->total) }}</td><td class="text-center"><span class="px-2 py-1 rounded-full text-xs bg-slate-100 capitalize">{{ str_replace('_',' ',$o->order_status) }}</span></td><td class="text-center"><span class="text-xs px-2 py-1 rounded-full {{ $o->payment_status=='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700' }}">{{ $o->payment_status }}</span></td></tr>@empty<tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada transaksi</td></tr>@endforelse</tbody>
    </table>
  </div>
  {{-- Mobile cards --}}
  <div class="sm:hidden divide-y">
    @forelse($recent as $o)
    <a href="{{ route('orders.show',$o) }}" class="flex justify-between gap-3 p-3 active:bg-slate-50">
      <div class="min-w-0"><div class="font-mono text-xs font-semibold truncate">{{ $o->order_number }}</div><div class="text-sm truncate">{{ $o->customer->name ?? 'Walk-in' }}</div><div class="text-xs text-slate-500 capitalize">{{ str_replace('_',' ',$o->order_status) }} • {{ $o->payment_status }}</div></div>
      <div class="text-right shrink-0"><div class="font-semibold text-sm">{{ money($o->total) }}</div><div class="text-xs text-slate-500">{{ $o->order_date? $o->order_date->format('d/m') : '' }}</div></div>
    </a>
    @empty <p class="py-8 text-center text-sm text-slate-400">Belum ada transaksi</p> @endforelse
  </div>
</div>
@endsection
