@extends('layouts.app')
@section('title','Order '.$order->order_number)
@section('content')
<div class="flex flex-col gap-3 mb-4">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0"><h1 class="text-base sm:text-xl font-bold font-mono truncate">{{ $order->order_number }}</h1><p class="text-xs sm:text-sm text-slate-500 truncate">{{ $order->branch->name }} • {{ $order->order_date->format('d M Y') }} • Kasir: {{ $order->cashier->name }}</p>
    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm">
      <span>Customer: <b id="orderCustName">{{ $order->customer->name ?? 'Walk-in' }}</b> <span class="text-slate-500">{{ $order->customer->phone ?? '' }} @if($order->customer) • {{ $order->customer->code }} @endif</span></span>
      @if(!in_array($order->order_status, ['picked_up','cancelled']))
        <button type="button" onclick="toggleOrderCustomerForm()" class="text-xs bg-white border border-slate-200 px-3 py-1.5 rounded-full hover:bg-slate-50">✏️ Ganti</button>
      @endif
    </div>
    @if(!in_array($order->order_status, ['picked_up','cancelled']))
    <div id="orderCustomerForm" class="hidden mt-2 bg-white border border-indigo-200 rounded-xl p-3 max-w-md">
      <form method="POST" action="{{ route('orders.customer', $order) }}" onsubmit="if(!document.getElementById('order_customer_id').value){ alert('Pilih customer dulu'); return false; } return confirm('Ganti customer ke yang dipilih?')">
        @csrf
        <input type="hidden" name="customer_id" id="order_customer_id" value="">
        <label class="text-xs font-semibold text-slate-600">Cari customer (nama / HP / kode)</label>
        <div class="relative mt-1">
          <input id="orderCustomerSearch" type="text" placeholder="Ketik nama atau HP" autocomplete="off" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
          <div id="orderCustomerResults" class="absolute z-20 w-full bg-white border border-slate-200 rounded-xl shadow-xl mt-1 hidden max-h-48 overflow-y-auto"></div>
        </div>
        <div id="orderCustomerSelected" class="hidden mt-2 bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-2 text-sm flex justify-between items-center gap-2">
          <span><b id="orderSelectedName">-</b> <span id="orderSelectedPhone" class="text-slate-500"></span></span>
          <button type="button" onclick="clearOrderCustomerSel()" class="w-7 h-7 grid place-items-center rounded-full hover:bg-white text-slate-500">×</button>
        </div>
        <div class="mt-2 flex gap-2">
          <button class="flex-1 bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold">Simpan Customer</button>
          <button type="button" onclick="toggleOrderCustomerForm()" class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm">Batal</button>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">Hanya bisa diganti selama status belum <b>picked_up</b> / cancelled.</p>
      </form>
    </div>
    @endif
    </div>
    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
      <a href="{{ route('orders.print',$order) }}" target="_blank" class="flex-1 sm:flex-none text-center border border-slate-200 bg-white px-4 py-3 sm:py-2 rounded-xl text-sm font-medium">🖨️ Cetak</a>
      <a href="{{ route('orders.receipt',$order) }}" target="_blank" class="flex-1 sm:flex-none text-center border border-slate-200 bg-white px-4 py-3 sm:py-2 rounded-xl text-sm font-medium">🧾 Struk</a>
      <form method="POST" action="{{ route('orders.whatsapp',$order) }}" class="flex-1 sm:flex-none" onsubmit="return confirm('Kirim struk via WhatsApp ke {{ $order->customer->phone ?? '' }}?')">@csrf<button class="w-full bg-emerald-600 active:bg-emerald-700 text-white px-4 py-3 sm:py-2 rounded-xl text-sm font-semibold">💬 Kirim WA</button></form>
    </div>
  </div>
  @if(request('fresh'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">Order berhasil! <a href="{{ route('orders.print',$order) }}" target="_blank" class="underline font-semibold">Cetak struk</a> • <a href="{{ route('pos.index') }}" class="underline">Kembali ke POS</a></div>@endif
</div>
<div class="grid lg:grid-cols-3 gap-3 sm:gap-4">
  <div class="lg:col-span-2 space-y-3 sm:space-y-4">
    <div class="bg-white rounded-2xl border overflow-hidden">
      <div class="p-3 sm:p-4 border-b"><h3 class="font-semibold text-sm sm:text-base">Items</h3></div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[420px]"><thead class="bg-slate-50 text-slate-500 text-xs"><tr><th class="text-left p-2 sm:p-3">Produk</th><th class="text-center">Qty</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr></thead><tbody>@foreach($order->items as $it)<tr class="border-t"><td class="p-2 sm:p-3"><div class="font-medium text-sm">{{ $it->product_name }}</div><div class="text-xs text-slate-500">{{ $it->sku }}</div></td><td class="text-center text-sm whitespace-nowrap">{{ rtrim(rtrim(number_format($it->quantity,3,',','.'),'0'),',') }} {{ $it->unit }}</td><td class="text-right text-sm">{{ money($it->price) }}</td><td class="text-right font-medium text-sm">{{ money($it->subtotal) }}</td></tr>@endforeach</tbody></table>
      </div>
      <div class="p-3 sm:p-4 space-y-1.5 text-sm border-t bg-slate-50/50">
        <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Diskon</span><span>-{{ money($order->discount) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Pajak</span><span>{{ money($order->tax) }}</span></div>
        <div class="flex justify-between font-bold text-base border-t pt-2"><span>Total</span><span class="text-indigo-600">{{ money($order->total) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Dibayar</span><span class="text-emerald-600 font-semibold">{{ money($order->paid_amount) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Kembalian</span><span>{{ money($order->change_amount) }}</span></div>
        <div class="flex justify-between items-center pt-1"><span class="text-slate-500">Status Bayar</span><span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $order->payment_status=='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700' }}">{{ $order->payment_status }}</span></div>
      </div>
    </div>
    <div class="bg-white rounded-2xl border p-3 sm:p-4">
      <h3 class="font-semibold text-sm sm:text-base mb-3">Pembayaran</h3>
      @forelse($order->payments as $p)<div class="flex flex-col sm:flex-row sm:justify-between gap-1 text-sm py-2.5 border-b last:border-0"><span class="text-xs sm:text-sm">{{ $p->payment_number }} • {{ $p->payment_method }} • {{ $p->paid_at->format('d/m/Y H:i') }}</span><span class="font-semibold">{{ money($p->amount) }}</span></div>@empty<p class="text-sm text-slate-400 py-2">Belum ada pembayaran</p>@endforelse
      @if($order->payment_status!='paid' && $order->order_status!='cancelled')
      <form method="POST" action="{{ route('orders.payment',$order) }}" class="mt-4 grid grid-cols-1 sm:flex gap-2">@csrf<input name="amount" type="number" step="0.01" placeholder="Nominal" required class="flex-1 border border-slate-200 rounded-xl px-3 py-3 text-sm"><select name="payment_method" class="border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white"><option value="cash">Cash</option><option value="transfer">Transfer</option><option value="qris">QRIS</option><option value="e_wallet">E-Wallet</option></select><button class="bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-semibold">Tambah Bayar</button></form>
      @endif
    </div>
  </div>
  <div class="space-y-3 sm:space-y-4">
    <div class="bg-white rounded-2xl border p-3 sm:p-4">
      <h3 class="font-semibold text-sm sm:text-base mb-3">Status Laundry</h3>
      <div class="flex flex-wrap gap-1.5 mb-3">@foreach(['received','washing','drying','ironing','ready','picked_up'] as $s)<span class="px-2.5 py-1.5 rounded-full text-xs font-medium {{ $order->order_status==$s?'bg-indigo-600 text-white':'bg-slate-100 text-slate-600' }}">{{ $s }}</span>@endforeach</div>
      @if(!in_array($order->order_status,['picked_up','cancelled']))
      <form method="POST" action="{{ route('orders.status',$order) }}" class="flex gap-2">@csrf<select name="order_status" class="flex-1 border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white">@foreach(['received','washing','drying','ironing','ready','picked_up'] as $s)<option value="{{ $s }}" @selected($order->order_status==$s)>{{ $s }}</option>@endforeach</select><button class="bg-slate-900 text-white px-4 py-3 rounded-xl text-sm font-semibold">Update</button></form>
      <form method="POST" action="{{ route('orders.cancel',$order) }}" class="mt-3" onsubmit="return confirm('Cancel order?')">@csrf<input name="cancel_reason" placeholder="Alasan cancel" required class="w-full border border-slate-200 rounded-xl px-3 py-3 text-sm mb-2"><button class="w-full bg-rose-600 active:bg-rose-700 text-white py-3 rounded-xl text-sm font-semibold">Cancel Order</button></form>
      @else
      <p class="text-sm text-slate-500 capitalize">Status: {{ $order->order_status }}</p>
      @endif
    </div>
    @if(!empty($order->laundry_details))
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3 sm:p-4">
      <h3 class="font-semibold text-sm mb-2">👕 Rincian Laundry</h3>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3 text-sm">
        @php $d=$order->laundry_details; $map=[]; try{ $map=\App\Models\LaundryItemType::whereIn('code', array_filter(array_keys($d), fn($k)=>!in_array($k,['catatan','lainnya_desc'])))->pluck('name','code')->toArray(); }catch(\Throwable $e){} @endphp
        @foreach($d as $k=>$v)
          @if(!in_array($k,['catatan','lainnya_desc']) && !empty($v))<div class="bg-white rounded-xl border px-3 py-2"><span class="text-xs text-slate-500">{{ $map[$k] ?? ucfirst(str_replace('_',' ',$k)) }}</span><div class="font-bold">{{ $v }} pcs</div></div>@endif
        @endforeach
      </div>
      @if(!empty($d['lainnya_desc']))<div class="mt-2 text-sm">Ket. Lainnya: <b>{{ $d['lainnya_desc'] }}</b></div>@endif
      @if(!empty($d['catatan']))<div class="mt-1 text-sm">Catatan: <b>{{ $d['catatan'] }}</b></div>@endif
      @if(!empty($order->notes))<div class="mt-1 text-xs text-slate-500">Notes order: {{ $order->notes }}</div>@endif
    </div>
    @endif
    <div class="bg-white rounded-2xl border p-3 sm:p-4 text-sm">
      <h3 class="font-semibold mb-2">Info</h3>
      <div class="space-y-1 text-sm text-slate-600"><div>Pickup: <b>{{ $order->pickup_date ? $order->pickup_date->format('d M Y') : '-' }}</b></div><div>Catatan: {{ $order->notes ?? '-' }}</div>@if($order->cancel_reason)<div class="text-rose-600">Cancel: {{ $order->cancel_reason }}</div>@endif</div>
    </div>
  </div>
</div>
@if(!in_array($order->order_status,['cancelled']))
<div class="mt-4 bg-white rounded-2xl border p-3 sm:p-4">
  <h3 class="font-semibold text-sm mb-3">Aksi Struk</h3>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
    <a href="{{ route('orders.print',$order) }}" target="_blank" class="text-center bg-slate-900 text-white px-4 py-3 rounded-xl text-sm font-semibold">🖨️ Cetak Struk</a>
    <form method="POST" action="{{ route('orders.whatsapp',$order) }}" class="contents sm:block">@csrf<button class="w-full bg-emerald-600 active:bg-emerald-700 text-white px-4 py-3 rounded-xl text-sm font-semibold">💬 Kirim WA</button></form>
    <a href="{{ route('orders.index') }}" class="text-center border border-slate-200 bg-white px-4 py-3 rounded-xl text-sm font-semibold">✕ Close</a>
  </div>
  <div class="grid grid-cols-2 gap-2 mt-2">
    <a href="{{ route('orders.print',$order) }}" target="_blank" onclick="event.preventDefault(); window.open('{{ route('orders.print',$order) }}','_blank'); setTimeout(()=>{ if(confirm('Kirim juga via WA?')) document.getElementById('waBothForm').submit(); },800)" class="text-center bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-semibold">🖨️ + 💬 Cetak & WA</a>
    <form id="waBothForm" method="POST" action="{{ route('orders.whatsapp',$order) }}" class="hidden">@csrf</form>
    <a href="{{ route('pos.index') }}" class="text-center border border-slate-200 bg-white px-4 py-3 rounded-xl text-sm font-semibold">🛒 POS Baru</a>
  </div>
  <p class="text-[11px] text-slate-500 mt-2 text-center">Cetak: buka tab print • WA: kirim link wa.me (atau API jika dikonfigurasi) • Cetak & WA: buka cetak lalu kirim WA</p>
</div>
@endif
@push('scripts')
@if(!in_array($order->order_status, ['picked_up','cancelled']))
<script>
function toggleOrderCustomerForm(){ let el=document.getElementById('orderCustomerForm'); el.classList.toggle('hidden'); if(!el.classList.contains('hidden')) document.getElementById('orderCustomerSearch').focus(); }
function clearOrderCustomerSel(){ document.getElementById('order_customer_id').value=''; document.getElementById('orderCustomerSelected').classList.add('hidden'); document.getElementById('orderCustomerSearch').value=''; }
function selectOrderCustomer(id,name,phone){ document.getElementById('order_customer_id').value=id; document.getElementById('orderSelectedName').textContent=name; document.getElementById('orderSelectedPhone').textContent=phone; document.getElementById('orderCustomerSelected').classList.remove('hidden'); document.getElementById('orderCustomerResults').classList.add('hidden'); document.getElementById('orderCustomerSearch').value=''; }
let ocTimer=null;
let ocEl=document.getElementById('orderCustomerSearch');
let ocBox=document.getElementById('orderCustomerResults');
if(ocEl){
  ocEl.addEventListener('input', function(){
    let q=this.value.trim(); if(!q){ ocBox.classList.add('hidden'); return; }
    clearTimeout(ocTimer);
    ocTimer=setTimeout(()=>{
      fetch('/customers-search?q='+encodeURIComponent(q)).then(r=>r.json()).then(data=>{
        if(!data.length){ ocBox.innerHTML='<div class="p-3 text-sm text-slate-400">Tidak ditemukan</div>'; ocBox.classList.remove('hidden'); return; }
        ocBox.innerHTML = data.map(c=> '<button type="button" onclick="selectOrderCustomer('+c.id+',\''+c.name.replace(/\'/g,'&#39;')+'\',\''+(c.phone||'')+'\')" class="w-full text-left px-3 py-2.5 hover:bg-slate-50 text-sm border-b last:border-0"><b>'+c.name+'</b><br><span class="text-slate-500 text-xs">'+(c.phone||'-')+' • '+c.code+'</span></button>').join('');
        ocBox.classList.remove('hidden');
      });
    },250);
  });
  document.addEventListener('click', e=>{
    if(!e.target.closest('#orderCustomerSearch') && !e.target.closest('#orderCustomerResults')) ocBox.classList.add('hidden');
  });
}
</script>
@endif
@endpush
@endsection
