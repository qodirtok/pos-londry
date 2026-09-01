@extends('layouts.app')
@section('title','Order '.$order->order_number)
@push('head')
<style>
  /* Flat UI overrides for Order Show */
  .order-action-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem}
  @media(min-width:640px){.order-action-grid{grid-template-columns:repeat(4,1fr)}}
  .action-btn{display:flex;flex-direction:column;align-items:center;gap:.375rem;padding:.75rem .5rem;border-radius:.75rem;font-weight:600;font-size:.8125rem;text-decoration:none;transition:all .15s ease;cursor:pointer;border:0;background:transparent}
  .action-btn.active{background-color:#4f46e5;color:#fff}
  .action-btn.inactive{background:#f8fafc;border:1px solid #e2e8f0;color:#475569}
  .action-btn .act-icon{display:flex;align-items:center;justify-content:center;width:1.5rem;height:1.5rem}
  @media(min-width:640px){.action-btn .act-icon{width:2rem;height:2rem}}
  .order-flat-icon{width:1.1em;height:1.1em;display:inline-block;vertical-align:-0.18em;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .order-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);z-index:80;display:none;align-items:center;padding:1rem;justify-content:center}
  .order-modal-backdrop.is-open{display:flex}
  .order-modal{background:#fff;width:100%;max-width:420px;border-radius:1.25rem;display:flex;flex-direction:column;overflow:hidden;animation:posSlideUp .25s cubic-bezier(.22,1,.36,1)}
  .order-modal-header{padding:1rem 1.1rem .75rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f1f5f9}
  .order-modal-header h3{font-weight:700;font-size:1rem;color:#0f172a;margin:0;display:flex;align-items:center;gap:.5rem}
  .order-modal-header .close-btn{width:2rem;height:2rem;border:0;background:#f1f5f9;border-radius:.6rem;color:#475569;cursor:pointer;display:grid;place-items:center}
  .order-modal-body{padding:1rem 1.1rem}
  .order-modal-footer{padding:.75rem 1.1rem;border-top:1px solid #f1f5f9;display:grid;grid-template-columns:1fr 1fr;gap:.5rem;background:#fafbfc}
  .order-modal-footer .btn{padding:.75rem;border-radius:.75rem;font-weight:600;font-size:.9rem;border:0;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:.4rem}
  .order-modal-footer .btn-primary{background:#4f46e5;color:#fff}
  .order-modal-footer .btn-secondary{background:#f1f5f9;color:#334155}
  @keyframes posSlideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>
@endpush
@section('content')
<div class="flex flex-col gap-3 mb-4">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <h1 class="text-base sm:text-xl font-bold font-mono truncate">{{ $order->order_number }}</h1>
      <p class="text-xs sm:text-sm text-slate-500 truncate">{{ $order->branch->name }} • {{ $order->order_date->format('d M Y') }} • Kasir: {{ $order->cashier->name }}</p>
    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm">
      <span>Customer: <b id="orderCustName">{{ $order->customer->name ?? 'Walk-in' }}</b> <span class="text-slate-500">{{ $order->customer->phone ?? '' }} @if($order->customer) • {{ $order->customer->code }} @endif</span></span>
      @if(!in_array($order->order_status, ['picked_up','cancelled']))
        <button type="button" onclick="openModal('#editCustomerModal')" class="text-xs bg-white border border-slate-200 px-3 py-1.5 rounded-full hover:bg-slate-50">
          <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:.85rem;height:.85rem"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.25 4.25 0 01-1.897 1.21l-2.25.5a.75.75 0 01-.906-.906 4.25 4.25 0 011.21-1.897L16.862 4.487zm0 0L19.5 7.125"/></svg> Ganti</button>
      @endif
    </div>
    </div>
    <div class="flex items-center gap-2">
      <button onclick="openReceiptModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">
        <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:1rem;height:1rem"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Struk
      </button>
    </div>
  </div>
  @if(request('fresh'))
  <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
    Order berhasil!
    <button onclick="openReceiptModal()" class="underline font-semibold">Cetak struk</button>
    • <a href="{{ route('pos.index') }}" class="underline">POS Baru</a>
  </div>
  @endif
</div>

<div class="grid lg:grid-cols-3 gap-3 sm:gap-4">
  <div class="lg:col-span-2 space-y-3 sm:space-y-4">
    <div class="bg-white rounded-2xl border overflow-hidden">
      <div class="p-3 sm:p-4 border-b"><h3 class="font-semibold text-sm sm:text-base">Items</h3></div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[420px]">
          <thead class="bg-slate-50 text-slate-500">
            <tr><th class="text-left p-2 sm:p-3">Produk</th><th class="text-center">Qty</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr>
          </thead>
          <tbody>
            @foreach($order->items as $it)
            <tr class="border-t">
              <td class="p-2 sm:p-3"><div class="font-medium text-sm">{{ $it->product_name }}</div><div class="text-xs text-slate-500">{{ $it->sku }}</div></td>
              <td class="text-center text-sm whitespace-nowrap">{{ rtrim(rtrim(number_format($it->quantity,3,',','.'),'0'),',') }} {{ $it->unit }}</td>
              <td class="text-right text-sm">{{ money($it->price) }}</td>
              <td class="text-right font-medium text-sm">{{ money($it->subtotal) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
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
      @forelse($order->payments as $p)
      <div class="flex flex-col sm:flex-row sm:justify-between gap-1 text-sm py-2.5 border-b last:border-0">
        <span class="text-xs sm:text-sm">{{ $p->payment_number }} • {{ $p->payment_method }} • {{ $p->paid_at->format('d/m/Y H:i') }}</span>
        <span class="font-semibold">{{ money($p->amount) }}</span>
      </div>
      @empty
      <p class="text-sm text-slate-400 py-2">Belum ada pembayaran</p>
      @endforelse
      @if($order->payment_status!='paid' && $order->order_status!='cancelled')
      <form method="POST" action="{{ route('orders.payment',$order) }}" class="mt-4 grid grid-cols-1 sm:flex gap-2" onsubmit="return handleSubmitForm(this, event)">
        @csrf
        <input name="amount" type="number" step="0.01" placeholder="Nominal" required class="flex-1 border border-slate-200 rounded-xl px-3 py-3 text-sm">
        <select name="payment_method" class="border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white">
          <option value="cash">Cash</option><option value="transfer">Transfer</option><option value="qris">QRIS</option><option value="e_wallet">E-Wallet</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2">
          <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:.95rem;height:.95rem"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
          Tambah Bayar
        </button>
      </form>
      @endif
    </div>
  </div>
  <div class="space-y-3 sm:space-y-4">
    <div class="bg-white rounded-2xl border p-3 sm:p-4">
      <h3 class="font-semibold text-sm sm:text-base mb-3">Status Laundry</h3>
      <div class="flex flex-wrap gap-1.5 mb-3">
        @foreach(['received','washing','drying','ironing','ready','picked_up'] as $s)
        <span class="px-2.5 py-1.5 rounded-full text-xs font-medium {{ $order->order_status==$s?'bg-indigo-600 text-white':'bg-slate-100 text-slate-600' }}">{{ $s }}</span>
        @endforeach
      </div>
      @if(!in_array($order->order_status,['picked_up','cancelled']))
      <form method="POST" action="{{ route('orders.status',$order) }}" class="flex gap-2" onsubmit="return handleSubmitForm(this, event)">
        @csrf
        <select name="order_status" class="flex-1 border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white">
          @foreach(['received','washing','drying','ironing','ready','picked_up'] as $s)
          <option value="{{ $s }}" @selected($order->order_status==$s)>{{ $s }}</option>
          @endforeach
        </select>
        <button type="submit" class="bg-slate-900 text-white px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2">
          <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:.95rem;height:.95rem"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
          Update
        </button>
      </form>
      <form method="POST" action="{{ route('orders.cancel',$order) }}" class="mt-3" onsubmit="return handleSubmitForm(this, event, true)">
        @csrf
        <input name="cancel_reason" placeholder="Alasan cancel" required class="w-full border border-slate-200 rounded-xl px-3 py-3 text-sm mb-2">
        <button type="submit" class="w-full bg-rose-600 active:bg-rose-700 text-white py-3 rounded-xl text-sm font-semibold flex items-center justify-center gap-2">
          <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:.95rem;height:.95rem"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
          Cancel Order
        </button>
      </form>
      @else
      <p class="text-sm text-slate-500 capitalize">Status: {{ $order->order_status }}</p>
      @endif
    </div>
    @if(!empty($order->laundry_details))
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3 sm:p-4">
      <h3 class="font-semibold text-sm mb-2">
        <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:.95rem;height:.95rem;color:#b45309"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        Rincian Laundry
      </h3>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3 text-sm">
        @php $d=$order->laundry_details; $map=[]; try{ $codes=array_keys(array_filter($d, fn($k)=>!in_array($k,['catatan','lainnya_desc']), ARRAY_FILTER_USE_KEY)); $map=\App\Models\LaundryItemType::whereIn('code',$codes)->pluck('name','code')->toArray(); }catch(\Throwable $e){} @endphp
        @foreach($d as $k=>$v)
          @if(!in_array($k,['catatan','lainnya_desc']) && !empty($v))
          <div class="bg-white rounded-xl border px-3 py-2">
            <span class="text-xs text-slate-500">{{ $map[$k] ?? ucfirst(str_replace('_',' ',$k)) }}</span>
            <div class="font-bold">{{ $v }} pcs</div>
          </div>
          @endif
        @endforeach
      </div>
      @if(!empty($d['lainnya_desc']))<div class="mt-2 text-sm">Ket. Lainnya: <b>{{ $d['lainnya_desc'] }}</b></div>@endif
      @if(!empty($d['catatan']))<div class="mt-1 text-sm">Catatan: <b>{{ $d['catatan'] }}</b></div>@endif
      @if(!empty($order->notes))<div class="mt-1 text-xs text-slate-500">Notes order: {{ $order->notes }}</div>@endif
    </div>
    @endif
    <div class="bg-white rounded-2xl border p-3 sm:p-4 text-sm">
      <h3 class="font-semibold mb-2">Info</h3>
      <div class="space-y-1 text-sm text-slate-600">
        <div>Pickup: <b>{{ $order->pickup_date ? $order->pickup_date->format('d M Y') : '-' }}</b></div>
        <div>Catatan: {{ $order->notes ?? '-' }}</div>
        @if($order->cancel_reason)<div class="text-rose-600">Cancel: {{ $order->cancel_reason }}</div>@endif
      </div>
    </div>
  </div>
</div>

{{-- Modals --}}
<div id="editCustomerModal" class="order-modal-backdrop">
  <div class="order-modal">
    <div class="order-modal-header">
      <h3>Ganti Customer</h3>
      <button type="button" onclick="closeModal('#editCustomerModal')" class="close-btn">×</button>
    </div>
    <div class="order-modal-body">
      <form method="POST" action="{{ route('orders.customer',$order) }}" onsubmit="return handleSubmitForm(this, event)">
        @csrf
        <div class="space-y-3">
          <div>
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Cari Customer</label>
            <input type="text" id="custSearchInput" placeholder="Nama / HP / Code" class="w-full border border-slate-200 rounded-xl px-3 py-3 text-sm">
            <div id="custResults" class="hidden mt-2 bg-white border rounded-xl overflow-hidden shadow-sm"></div>
          </div>
          <input type="hidden" name="customer_id" id="newCustId" value="{{ $order->customer_id }}">
          <div id="selectedCustBox" class="bg-indigo-50 border border-indigo-100 rounded-xl p-3 flex justify-between items-center">
            <div class="min-w-0">
              <div class="font-bold text-sm" id="selectedCustName">{{ $order->customer->name ?? 'Walk-in' }}</div>
              <div class="text-xs text-slate-500" id="selectedCustPhone">{{ $order->customer->phone ?? '' }}</div>
            </div>
          </div>
          <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl text-sm font-semibold mt-4">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="receiptModal" class="order-modal-backdrop">
  <div class="order-modal">
    <div class="order-modal-header">
      <h3>
        <svg class="order-flat-icon" viewBox="0 0 24 24" style="color:#10b981"><path d="m20 6-11 11-5-5"/></svg>
        Aksi Struk
      </h3>
      <button type="button" onclick="closeModal('#receiptModal')" class="close-btn">×</button>
    </div>
    <div class="order-modal-body">
      <div class="text-center py-4">
        <div class="mx-auto w-16 h-16 rounded-full grid place-items-center mb-3" style="background:#d1fae5">
          <svg viewBox="0 0 24 24" style="width:2rem;height:2rem;color:#047857;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><path d="m20 6-11 11-5-5"/></svg>
        </div>
        <p class="text-xs text-slate-500 uppercase tracking-wider">Order {{ $order->order_number }}</p>
        <p class="text-3xl font-bold text-slate-800 mt-1">{{ money($order->total) }}</p>
      </div>
      
      <div class="grid grid-cols-2 gap-2 mt-4">
        <button onclick="receiptAction('print')" class="w-full bg-indigo-600 text-white py-3 rounded-xl text-sm font-semibold flex items-center justify-center gap-2">
          <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:1rem;height:1rem"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Cetak
        </button>
        <button onclick="receiptAction('wa')" class="w-full bg-emerald-600 text-white py-3 rounded-xl text-sm font-semibold flex items-center justify-center gap-2">
          <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:1rem;height:1rem"><path d="M21 11.5a8.4 8.4 0 0 1-12.6 7.3L3 21l1.9-5.4A8.4 8.4 0 1 1 21 11.5Z"/></svg>
          Kirim WA
        </button>
        <button onclick="receiptAction('printwa')" class="w-full col-span-2 bg-slate-900 text-white py-3.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2">
          <svg class="order-flat-icon" viewBox="0 0 24 24" style="width:1rem;height:1rem"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Cetak & Kirim WA
        </button>
      </div>
    </div>
    <div class="bg-slate-50 p-4 border-t flex justify-center">
      <button onclick="closeModal('#receiptModal')" class="text-sm font-semibold text-slate-500">Tutup</button>
    </div>
  </div>
</div>

@push('scripts')

<script>
function openModal(sel){ document.querySelector(sel).classList.add('is-open'); }
function closeModal(sel){ document.querySelector(sel).classList.remove('is-open'); }

async function handleSubmitForm(form, event, confirmMsg=false){
  if(confirmMsg){
    if(!confirm(confirmMsg === true ? 'Yakin ingin melakukan aksi ini?' : confirmMsg)) return false;
  }
  event.preventDefault();
  const fd = new FormData(form);
  try{
    let res = await fetch(form.action, {method:'POST', body: fd, headers:{'X-Requested-With':'XMLHttpRequest'}});
    let data = await res.json();
    if(res.ok){
      alert(data.message || 'Berhasil disimpan');
      location.reload();
    } else {
      alert(data.message || 'Gagal menyimpan');
    }
  }catch(e){ alert(e.message); }
  return false;
}

function openReceiptModal(){ openModal('#receiptModal'); }

async function receiptAction(act){
  const id = "{{ $order->id }}";
  if(act==='print'){ 
    window.open('/orders/'+id+'/print','_blank','width=400,height=600'); 
  } else if(act==='wa'){ 
    await sendWhatsappOrder(id);
  } else if(act==='printwa'){ 
    window.open('/orders/'+id+'/print','_blank','width=400,height=600'); 
    setTimeout(()=>sendWhatsappOrder(id), 500);
  }
}

async function sendWhatsappOrder(id){
  // Redirect langsung ke WhatsApp tanpa Swal popup
  window.open('/orders/'+id+'/whatsapp','_blank','width=400,height=600');
}

// Customer Search logic for Edit Customer Modal
let cTimer;
document.getElementById('custSearchInput')?.addEventListener('input', function(){
  let q=this.value.trim(); let box=document.getElementById('custResults');
  if(q.length < 2){ box.classList.add('hidden'); return; }
  clearTimeout(cTimer);
  cTimer=setTimeout(()=>{
    fetch('/customers-search?q='+encodeURIComponent(q).title || 'Hapus?')) { r=>r.json()).then(data=>{
      if(!data.length){ box.innerHTML='<div class="p-3 text-sm text-slate-400">Tidak ditemukan</div>'; box.classList.remove('hidden'; } return; }
      box.innerHTML = data.map(c=> `<button type="button" onclick="selectNewCust(${c.id},'${c.name.replace(/'/g, "\\'")}','${c.phone||''}')" class="w-full text-left px-3 py-2.5 hover:bg-slate-50 text-sm border-b last:border-0"><b>${c.name}</b><br><span class="text-slate-500 text-xs">${c.phone||''} • ${c.code}</span></button>`).join('');
      box.classList.remove('hidden');
    });
  },300);
});

function selectNewCust(id,name,phone){
  document.getElementById('newCustId').value = id;
  document.getElementById('selectedCustName').textContent = name;
  document.getElementById('selectedCustPhone').textContent = phone;
  document.getElementById('custResults').classList.add('hidden');
  document.getElementById('custSearchInput').value = '';
}
</script>
@endpush
@endsection
