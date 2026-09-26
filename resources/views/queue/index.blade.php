@extends('layouts.app')
@section('title','Antrian')
@push('head')
<style>
  .queue-card{background:#fff;border:1px solid #e7e2d9;border-radius:1rem;padding:1rem;display:flex;flex-direction:column;gap:.5rem;transition:all .15s ease}
  .queue-card:hover{border-color:#5eead4;box-shadow:0 2px 4px rgba(15,23,42,.06)}
  .queue-pos{background:#fef3c7;color:#92400e;width:2rem;height:2rem;border-radius:.5rem;display:grid;place-items:center;font-weight:700;font-size:.85rem;flex-shrink:0}
  .queue-order-num{font-family:ui-monospace,SFMono-Regular,monospace;font-size:.75rem;font-weight:600;color:#6b6357}
  .queue-cust-name{font-size:.95rem;font-weight:600;color:#2b2320;line-height:1.2}
  .queue-cust-phone{font-size:.75rem;color:#57503f}
  .queue-time{font-size:.7rem;color:#6b6357;display:flex;align-items:center;gap:.25rem}
  .queue-status-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .6rem;border-radius:9999px;font-size:.7rem;font-weight:600;line-height:1}
  .queue-action-btn{flex:1;padding:.5rem;border:0;border-radius:.6rem;font-size:.8rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:.3rem;transition:all .15s ease}
  .queue-action-btn:active{transform:scale(.97)}
  .queue-btn-next{background:#dcfce7;color:#15803d}
</style>
@endpush
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
  <h1 class="text-xl sm:text-2xl font-bold">Antrian Laundry</h1>
  <span class="text-xs sm:text-sm text-paper-500">Order dengan status <b>received</b></span>
</div>

<form class="bg-white rounded-2xl border p-3 sm:p-4 mb-4">
  <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="Cari no. order / customer / HP" class="sm:col-span-9 border border-paper-300 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-teal-500 outline-none">
    <button class="sm:col-span-3 bg-paper-900 text-white px-4 py-3 rounded-xl text-sm font-medium">Cari</button>
  </div>
</form>

@if($orders->count() === 0)
  <div class="bg-white rounded-2xl border p-12 text-center">
    <p class="text-4xl mb-2">✅</p>
    <p class="text-paper-500 text-sm">Tidak ada antrian. Semua order sudah diproses.</p>
  </div>
@else
  {{-- Desktop: cards grid --}}
  <div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($orders as $i => $o)
    <div class="queue-card">
      <div class="flex items-start gap-2">
        <div class="queue-pos">{{ ($orders->currentPage()-1) * $orders->perPage() + $i + 1 }}</div>
        <div class="flex-1 min-w-0">
          <div class="queue-order-num truncate">{{ $o->order_number }}</div>
          <div class="queue-cust-name truncate">{{ $o->customer->name ?? 'Walk-in' }}</div>
          <div class="queue-cust-phone">{{ $o->customer->phone ?? '-' }}</div>
        </div>
      </div>
      @if(!empty($o->laundry_details))
      <div class="text-xs text-paper-600 bg-amber-50 rounded-lg px-2.5 py-1.5 border border-amber-100">
        <b>{{ count(array_filter($o->laundry_details, fn($k) => !in_array($k, ['catatan','lainnya_desc']), ARRAY_FILTER_USE_KEY)) }}</b> jenis item laundry
      </div>
      @else
      <div class="text-xs text-paper-600 bg-paper-100 rounded-lg px-2.5 py-1.5 border border-paper-300">
        Order produk ({{ $o->items->count() }} item)
      </div>
      @endif
      <div class="flex items-center justify-between text-xs">
        <span class="queue-time">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.85rem;height:.85rem"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          {{ $o->order_date->format('d/m/Y H:i') }}
        </span>
        <span class="font-semibold text-teal-600">{{ money($o->total) }}</span>
      </div>
      <div class="flex gap-1.5 pt-1">
        <a href="{{ route('pos.edit',$o) }}" class="queue-action-btn queue-btn-next">Detail</a>
      </div>
    </div>
    @endforeach
  </div>
  <div class="hidden sm:block pt-4">{{ $orders->withQueryString()->links() }}</div>

  {{-- Mobile: stacked cards --}}
  <div class="sm:hidden space-y-2">
    @foreach($orders as $i => $o)
    <div class="queue-card">
      <div class="flex items-start gap-2">
        <div class="queue-pos">{{ ($orders->currentPage()-1) * $orders->perPage() + $i + 1 }}</div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <span class="queue-order-num truncate">{{ $o->order_number }}</span>
            <span class="text-xs text-paper-500 shrink-0">{{ $o->order_date->format('d/m H:i') }}</span>
          </div>
          <div class="queue-cust-name truncate">{{ $o->customer->name ?? 'Walk-in' }}</div>
          <div class="queue-cust-phone">{{ $o->customer->phone ?? '-' }}</div>
        </div>
      </div>
      <div class="flex items-center justify-between text-xs pt-1">
        @if(!empty($o->laundry_details))
          <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full text-[11px] font-semibold border border-amber-100">Laundry</span>
        @else
          <span class="bg-paper-100 text-paper-600 px-2 py-0.5 rounded-full text-[11px] font-semibold">Produk</span>
        @endif
        <span class="font-semibold text-teal-600">{{ money($o->total) }}</span>
      </div>
      <div class="flex gap-1.5 pt-1">
        <a href="{{ route('pos.edit',$o) }}" class="queue-action-btn queue-btn-next">Detail</a>
      </div>
    </div>
    @endforeach
    <div class="pt-2">{{ $orders->withQueryString()->links() }}</div>
  </div>
@endif

@endsection
