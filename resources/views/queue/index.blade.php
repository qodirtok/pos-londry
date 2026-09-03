@extends('layouts.app')
@section('title','Antrian')
@push('head')
<style>
  .queue-card{background:#fff;border:1px solid #e2e8f0;border-radius:1rem;padding:1rem;display:flex;flex-direction:column;gap:.5rem;transition:all .15s ease}
  .queue-card:hover{border-color:#818cf8;box-shadow:0 2px 4px rgba(15,23,42,.06)}
  .queue-card-header{display:flex;align-items:start;justify-content:between;gap:.5rem}
  .queue-pos{background:#fef3c7;color:#92400e;width:2rem;height:2rem;border-radius:.5rem;display:grid;place-items:center;font-weight:700;font-size:.85rem;flex-shrink:0}
  .queue-order-num{font-family:ui-monospace,SFMono-Regular,monospace;font-size:.75rem;font-weight:600;color:#475569}
  .queue-cust-name{font-size:.95rem;font-weight:600;color:#0f172a;line-height:1.2}
  .queue-cust-phone{font-size:.75rem;color:#64748b}
  .queue-time{font-size:.7rem;color:#94a3b8;display:flex;align-items:center;gap:.25rem}
  .queue-status-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .6rem;border-radius:9999px;font-size:.7rem;font-weight:600;line-height:1}
  .queue-action-btn{flex:1;padding:.5rem;border:0;border-radius:.6rem;font-size:.8rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:.3rem;transition:all .15s ease}
  .queue-action-btn:active{transform:scale(.97)}
  .queue-btn-edit{background:#eef2ff;color:#4f46e5}
  .queue-btn-next{background:#dcfce7;color:#15803d}
  .queue-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);z-index:80;display:none;align-items:center;padding:1rem;justify-content:center}
  .queue-modal-backdrop.is-open{display:flex}
  .queue-modal{background:#fff;width:100%;max-width:480px;border-radius:1.25rem;display:flex;flex-direction:column;overflow:hidden;animation:queueSlideUp .25s cubic-bezier(.22,1,.36,1)}
  .queue-modal-header{padding:1rem 1.1rem .75rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f1f5f9}
  .queue-modal-header h3{font-weight:700;font-size:1rem;color:#0f172a;margin:0}
  .queue-modal-header .close-btn{width:2rem;height:2rem;border:0;background:#f1f5f9;border-radius:.6rem;color:#475569;cursor:pointer;display:grid;place-items:center}
  .queue-modal-body{padding:1rem 1.1rem;overflow-y:auto;max-height:70vh}
  .queue-modal-footer{padding:.75rem 1.1rem;border-top:1px solid #f1f5f9;display:grid;grid-template-columns:1fr 1fr;gap:.5rem;background:#fafbfc}
  .queue-modal-footer .btn{padding:.75rem;border-radius:.75rem;font-weight:600;font-size:.9rem;border:0;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:.4rem}
  .queue-modal-footer .btn-primary{background:#4f46e5;color:#fff}
  .queue-modal-footer .btn-secondary{background:#f1f5f9;color:#334155}
  @keyframes queueSlideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>
@endpush
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
  <h1 class="text-xl sm:text-2xl font-bold">Antrian Laundry</h1>
  <span class="text-xs sm:text-sm text-slate-500">Order dengan status <b>received</b></span>
</div>

<form class="bg-white rounded-2xl border p-3 sm:p-4 mb-4">
  <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
    <input name="search" value="{{ request('search') }}" placeholder="Cari no. order / customer / HP" class="sm:col-span-9 border border-slate-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
    <button class="sm:col-span-3 bg-slate-900 text-white px-4 py-3 rounded-xl text-sm font-medium">Cari</button>
  </div>
</form>

@if($orders->count() === 0)
  <div class="bg-white rounded-2xl border p-12 text-center">
    <p class="text-4xl mb-2">✅</p>
    <p class="text-slate-500 text-sm">Tidak ada antrian. Semua order sudah diproses.</p>
  </div>
@else
  {{-- Desktop: cards grid --}}
  <div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($orders as $i => $o)
    <div class="queue-card"
         data-order-id="{{ $o->id }}"
         data-order-number="{{ $o->order_number }}"
         data-order-status="{{ $o->order_status }}"
         data-order-notes="{{ $o->notes ?? '' }}">
      <div class="flex items-start gap-2">
        <div class="queue-pos">{{ ($orders->currentPage()-1) * $orders->perPage() + $i + 1 }}</div>
        <div class="flex-1 min-w-0">
          <div class="queue-order-num truncate">{{ $o->order_number }}</div>
          <div class="queue-cust-name truncate">{{ $o->customer->name ?? 'Walk-in' }}</div>
          <div class="queue-cust-phone">{{ $o->customer->phone ?? '-' }}</div>
        </div>
      </div>
      @if(!empty($o->laundry_details))
      <div class="text-xs text-slate-600 bg-amber-50 rounded-lg px-2.5 py-1.5 border border-amber-100">
        <b>{{ count(array_filter($o->laundry_details, fn($k) => !in_array($k, ['catatan','lainnya_desc']), ARRAY_FILTER_USE_KEY)) }}</b> jenis item laundry
      </div>
      @else
      <div class="text-xs text-slate-600 bg-slate-50 rounded-lg px-2.5 py-1.5 border border-slate-200">
        Order produk ({{ $o->items->count() }} item)
      </div>
      @endif
      <div class="flex items-center justify-between text-xs">
        <span class="queue-time">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.85rem;height:.85rem"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          {{ $o->order_date->format('d/m/Y H:i') }}
        </span>
        <span class="font-semibold text-indigo-600">{{ money($o->total) }}</span>
      </div>
      <div class="flex gap-1.5 pt-1">
        <a href="{{ route('orders.show',$o) }}" class="queue-action-btn queue-btn-next">Detail</a>
        <button type="button" class="queue-action-btn queue-btn-edit js-edit-queue">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.9rem;height:.9rem"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.25 4.25 0 01-1.897 1.21l-2.25.5a.75.75 0 01-.906-.906 4.25 4.25 0 011.21-1.897L16.862 4.487zm0 0L19.5 7.125"/></svg>
          Edit
        </button>
      </div>
    </div>
    @endforeach
  </div>
  <div class="hidden sm:block pt-4">{{ $orders->withQueryString()->links() }}</div>

  {{-- Mobile: stacked cards --}}
  <div class="sm:hidden space-y-2">
    @foreach($orders as $i => $o)
    <div class="queue-card"
         data-order-id="{{ $o->id }}"
         data-order-number="{{ $o->order_number }}"
         data-order-status="{{ $o->order_status }}"
         data-order-notes="{{ $o->notes ?? '' }}">
      <div class="flex items-start gap-2">
        <div class="queue-pos">{{ ($orders->currentPage()-1) * $orders->perPage() + $i + 1 }}</div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <span class="queue-order-num truncate">{{ $o->order_number }}</span>
            <span class="text-xs text-slate-500 shrink-0">{{ $o->order_date->format('d/m H:i') }}</span>
          </div>
          <div class="queue-cust-name truncate">{{ $o->customer->name ?? 'Walk-in' }}</div>
          <div class="queue-cust-phone">{{ $o->customer->phone ?? '-' }}</div>
        </div>
      </div>
      <div class="flex items-center justify-between text-xs pt-1">
        @if(!empty($o->laundry_details))
          <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full text-[11px] font-semibold border border-amber-100">Laundry</span>
        @else
          <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-[11px] font-semibold">Produk</span>
        @endif
        <span class="font-semibold text-indigo-600">{{ money($o->total) }}</span>
      </div>
      <div class="flex gap-1.5 pt-1">
        <a href="{{ route('orders.show',$o) }}" class="queue-action-btn queue-btn-next">Detail</a>
        <button type="button" class="queue-action-btn queue-btn-edit js-edit-queue">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.9rem;height:.9rem"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.25 4.25 0 01-1.897 1.21l-2.25.5a.75.75 0 01-.906-.906 4.25 4.25 0 011.21-1.897L16.862 4.487zm0 0L19.5 7.125"/></svg>
          Edit
        </button>
      </div>
    </div>
    @endforeach
    <div class="pt-2">{{ $orders->withQueryString()->links() }}</div>
  </div>
@endif

{{-- Edit modal --}}
<div id="editQueueModal" class="queue-modal-backdrop">
  <div class="queue-modal">
    <div class="queue-modal-header">
      <h3 id="editQueueTitle">Edit Antrian</h3>
      <button type="button" onclick="closeEditModal()" class="close-btn">×</button>
    </div>
    <form id="editQueueForm" method="POST" action="">
      @csrf
      <div class="queue-modal-body space-y-3">
        <div>
          <label class="text-xs font-semibold text-slate-500 mb-1 block">Status</label>
          <select name="order_status" id="editQueueStatus" class="w-full border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
            <option value="received">Received (Antrian)</option>
            <option value="ready">Ready</option>
            <option value="picked_up">Picked Up</option>
            <option value="complete">Complete (Barang)</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-500 mb-1 block">Catatan</label>
          <textarea name="notes" id="editQueueNotes" rows="3" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Tambah catatan (opsional)"></textarea>
        </div>
      </div>
      <div class="queue-modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.js-edit-queue').forEach(function(btn){
    btn.addEventListener('click', function(){
      const card = btn.closest('.queue-card');
      openEditModal(
        card.dataset.orderId,
        card.dataset.orderNumber,
        card.dataset.orderStatus,
        card.dataset.orderNotes || ''
      );
    });
  });
});
function openEditModal(id, orderNumber, status, notes){
  document.getElementById('editQueueTitle').textContent = 'Edit ' + orderNumber;
  document.getElementById('editQueueStatus').value = status;
  document.getElementById('editQueueNotes').value = notes;
  document.getElementById('editQueueForm').action = '/antrian/' + id + '/update';
  document.getElementById('editQueueModal').classList.add('is-open');
}
function closeEditModal(){
  document.getElementById('editQueueModal').classList.remove('is-open');
}
document.getElementById('editQueueModal')?.addEventListener('click', function(e){
  if(e.target === this) closeEditModal();
});
document.addEventListener('keydown', function(e){
  if(e.key === 'Escape') closeEditModal();
});
</script>
@endpush
@endsection
