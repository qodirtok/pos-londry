@extends('layouts.app')
@section('title','POS Kasir')
@section('content')
<div class="flex flex-col lg:flex-row gap-3 lg:gap-4 lg:h-[calc(100vh-88px)]">
  {{-- Produk kiri --}}
  <div class="flex-1 bg-white rounded-2xl border flex flex-col overflow-hidden min-h-[42vh] lg:min-h-0">
    <div class="p-3 sm:p-4 border-b space-y-3">
      <div class="relative">
        <input id="productSearch" type="text" inputmode="search" placeholder="Cari produk / SKU / scan barcode" class="w-full border border-slate-200 rounded-xl sm:rounded-2xl pl-10 pr-4 py-3 sm:py-3 text-[15px] focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" autofocus>
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
      </div>
      <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 -mx-1 px-1 snap-x">
        <button onclick="filterCat('')" data-cat="" class="catBtn snap-start shrink-0 px-3.5 py-2 rounded-full bg-slate-900 text-white text-sm font-medium">Semua</button>
        @foreach(\App\Models\Category::all() as $c)<button onclick="filterCat('{{ $c->id }}')" data-cat="{{ $c->id }}" class="catBtn snap-start shrink-0 px-3.5 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium whitespace-nowrap">{{ $c->name }}</button>@endforeach
      </div>
    </div>
    <div id="productGrid" class="flex-1 overflow-y-auto p-2 sm:p-4 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-3 content-start">
      @foreach($products as $p)
      <button onclick="addToCart({{ $p->id }})" data-cat="{{ $p->category_id }}" data-name="{{ strtolower($p->name) }}" data-sku="{{ strtolower($p->sku) }}" data-barcode="{{ strtolower($p->barcode ?? '') }}" class="productCard text-left border border-slate-200 rounded-xl sm:rounded-2xl p-3 hover:border-indigo-400 hover:shadow-sm active:scale-[0.98] transition bg-white flex flex-col">
        <div class="flex items-start justify-between gap-1">
          <span class="text-[11px] font-mono bg-slate-100 px-2 py-1 rounded-full truncate max-w-[72%]">{{ $p->sku }}</span>
          <span class="text-[11px] px-2 py-1 rounded-full shrink-0 {{ $p->type=='service'?'bg-indigo-100 text-indigo-700':'bg-emerald-100 text-emerald-700' }}">{{ $p->unit }}</span>
        </div>
        <div class="font-semibold text-[13px] sm:text-sm leading-tight mt-2 line-clamp-2 min-h-[2.4em]">{{ $p->name }}</div>
        <div class="text-[11px] text-slate-500 mt-1 truncate">{{ $p->category->name }}</div>
        <div class="font-bold text-indigo-600 mt-auto pt-2 text-sm">{{ money($p->price) }}</div>
      </button>
      @endforeach
    </div>
    <div class="lg:hidden px-3 py-2 border-t bg-slate-50 text-xs text-slate-500 text-center">Tap produk untuk tambah ke keranjang</div>
  </div>

  {{-- Keranjang kanan: di HP jadi card di bawah, di desktop fixed width --}}
  <div class="w-full lg:w-[400px] xl:w-[420px] bg-white rounded-2xl border flex flex-col overflow-hidden shrink-0">
    <div class="p-3 sm:p-4 border-b space-y-3">
      <label class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Customer</label>
      <div class="flex gap-2">
        <div class="flex-1 relative min-w-0">
          <input id="customerSearch" type="text" placeholder="Cari nama / HP" class="w-full border border-slate-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
          <div id="customerResults" class="absolute z-10 w-full bg-white border border-slate-200 rounded-xl shadow-xl mt-1.5 hidden max-h-52 overflow-y-auto"></div>
        </div>
        <a href="{{ route('customers.create') }}" target="_blank" class="shrink-0 px-3 sm:px-4 py-3 bg-slate-900 text-white rounded-xl text-sm font-semibold">+ Baru</a>
      </div>
      <div id="selectedCustomer" class="bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-2.5 text-sm flex justify-between items-center gap-2">
        <span class="min-w-0 truncate"><b id="custName">Walk-in Customer</b> <span id="custPhone" class="text-slate-500">000000</span></span>
        <button onclick="clearCustomer()" class="shrink-0 w-7 h-7 grid place-items-center rounded-full hover:bg-white text-slate-500">×</button>
      </div>
      <input type="hidden" id="customerId" value="{{ ($customers->firstWhere('phone','000000')->id ?? ($customers->first()->id ?? '')) }}">
    </div>

    {{-- Rincian Laundry DINAMIS — collapsed default, dropdown tambah, kosong default --}}
    <div class="mx-3 sm:mx-4 mb-3 bg-amber-50 border border-amber-200 rounded-xl overflow-hidden">
      <button type="button" onclick="toggleLaundry()" class="w-full flex items-center justify-between px-3 sm:px-4 py-2.5 text-left hover:bg-amber-100/60 transition">
        <span class="text-xs sm:text-sm font-bold text-amber-900 flex items-center gap-2">👕 Rincian Laundry <span id="laundryBadge" class="hidden bg-amber-900 text-amber-50 text-[10px] px-2 py-0.5 rounded-full">0 pcs</span></span>
        <span class="flex items-center gap-2"><span id="laundrySummaryText" class="hidden sm:inline text-[11px] text-amber-700/70 truncate max-w-[18ch]"></span><span id="laundryChevron" class="text-amber-700 transition-transform -rotate-90">▼</span></span>
      </button>
      <div id="laundryPanel" class="hidden px-3 sm:px-4 pb-3 sm:pb-4 space-y-3 border-t border-amber-200/60 pt-3">
        <p class="text-[11px] leading-relaxed text-amber-800/80">Expand untuk isi pcs. Kosong default — pilih jenis dari dropdown lalu <b>+ Tambah</b>. Tap ✕ di card untuk hapus baris. <a href="{{ route('laundry-types.index') }}" class="underline font-semibold">Kelola jenis</a></p>
        <div id="laundryGrid" class="flex flex-col gap-2 min-h-[0]">
          <!-- cards ditambah via dropdown -->
        </div>
        <div id="laundryEmpty" class="border border-dashed border-amber-300 rounded-xl bg-white/70 px-3 py-6 text-center">
          <p class="text-sm text-slate-500">Belum ada rincian. Pilih jenis di bawah lalu tambah.</p>
          <p class="text-[11px] text-slate-400 mt-1">Contoh: Baju, Celana, Sepatu, Tas — pcs akan masuk struk</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-xl p-2.5 sm:p-3 space-y-2.5">
          <label class="text-xs font-semibold text-slate-700">Tambah jenis ke rincian</label>
          <div class="flex flex-col sm:flex-row gap-2">
            <select id="laundrySelect" class="flex-1 min-w-0 border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white focus:ring-2 focus:ring-amber-400 outline-none">
              <option value="">— Pilih jenis —</option>
              @foreach($laundryTypes as $t)
              <option value="{{ $t->code }}" data-name="{{ $t->name }}" data-icon="{{ $t->icon ?? '📦' }}">{{ ($t->icon ?? '📦').' '.$t->name }}</option>
              @endforeach
            </select>
            <button type="button" onclick="addLaundryFromSelect()" class="shrink-0 bg-slate-900 active:bg-black text-white px-5 py-3 rounded-xl text-sm font-semibold whitespace-nowrap">+ Tambah</button>
          </div>
          <details class="group">
            <summary class="text-xs text-slate-500 cursor-pointer list-none flex items-center gap-1"><span class="group-open:rotate-90 transition">▶</span> Jenis belum ada? Buat baru</summary>
            <div class="mt-2 flex flex-col sm:flex-row gap-2">
              <input id="newLaundryName" type="text" placeholder="Nama jenis baru, cth: Gorden" class="flex-1 min-w-0 border border-slate-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-400 outline-none">
              <input id="newLaundryIcon" type="text" placeholder="Icon 🧹" class="w-full sm:w-24 border border-slate-200 rounded-xl px-3 py-3 text-sm text-center sm:text-left">
              <button type="button" onclick="createLaundryType()" class="shrink-0 bg-white border border-slate-200 active:bg-slate-50 text-slate-800 px-4 py-3 rounded-xl text-sm font-semibold whitespace-nowrap">Buat</button>
            </div>
          </details>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
          <label class="text-xs font-medium text-slate-600">Ket. Lainnya <span class="font-normal text-slate-400">(opsional)</span><input id="laundry_lainnya_desc" type="text" placeholder="Bed cover, gorden, dll" class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-400 outline-none"></label>
          <label class="text-xs font-medium text-slate-600">Catatan laundry <span class="font-normal text-slate-400">(masuk struk)</span><input id="laundry_catatan" type="text" placeholder="Noda di kerah, jangan pakai pewangi" class="mt-1 w-full border border-slate-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-400 outline-none"></label>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
          <span class="text-[11px] text-slate-500">Total pcs: <b id="laundryTotal" class="text-slate-800">0</b></span>
          <button type="button" onclick="clearLaundry()" class="text-xs text-slate-500 hover:text-slate-700 underline">Kosongkan rincian</button>
        </div>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-2 sm:space-y-3 min-h-[18vh] lg:min-h-0" id="cartItems">
      <div id="emptyCart" class="text-center py-8 sm:py-10 text-slate-400 text-sm">Keranjang kosong<br><span class="text-xs">Tap produk di atas untuk menambah</span></div>
    </div>

    <div class="border-t p-3 sm:p-4 space-y-3 bg-slate-50">
      <div class="space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span id="subtotal" class="font-medium">Rp 0</span></div>
        <div class="flex justify-between items-center gap-2">
          <span class="text-slate-500 shrink-0">Diskon</span>
          <div class="flex items-center gap-1.5">
            <input id="discount" type="number" value="0" min="0" inputmode="numeric" class="w-20 sm:w-24 border border-slate-200 rounded-xl px-2.5 py-2 text-right text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            <select id="discountType" class="border border-slate-200 rounded-xl px-2 py-2 text-xs bg-white"><option value="fixed">Rp</option><option value="percent">%</option></select>
          </div>
        </div>
        <div class="flex justify-between items-center"><span class="text-slate-500">Pajak</span><input id="tax" type="number" value="0" min="0" inputmode="numeric" class="w-24 sm:w-28 border border-slate-200 rounded-xl px-2.5 py-2 text-right text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></div>
        <div class="flex justify-between font-bold text-base sm:text-lg pt-2 border-t border-slate-200"><span>TOTAL</span><span id="grandTotal" class="text-indigo-600">Rp 0</span></div>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-semibold text-slate-600">Status laundry</label>
        <div class="grid grid-cols-2 gap-2">
          <button type="button" id="btnBaru" onclick="setLaundryStatus('received')" class="py-3 rounded-xl text-sm font-semibold border-2 border-slate-900 bg-slate-900 text-white">🟡 Baru</button>
          <button type="button" id="btnSelesai" onclick="setLaundryStatus('ready')" class="py-3 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">✅ Selesai</button>
        </div>
        <input type="hidden" id="orderStatus" value="received">
        <p class="text-[11px] text-slate-500">Baru = masuk antrian • Selesai = siap diambil, bisa langsung bayar</p>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <select id="paymentMethod" class="border border-slate-200 rounded-xl px-3 py-3 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none"><option value="cash">Cash</option><option value="transfer">Transfer</option><option value="qris">QRIS</option><option value="debit">Debit</option><option value="e_wallet">E-Wallet</option></select>
        <input id="paidAmount" type="number" inputmode="numeric" placeholder="Bayar" class="border border-slate-200 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
      </div>
      <div class="flex justify-between text-sm"><span class="text-slate-500">Kembalian</span><span id="change" class="font-semibold">Rp 0</span></div>
      <button onclick="checkout()" id="payBtn" class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-3.5 sm:py-3 rounded-xl text-[15px] shadow">BAYAR & CETAK</button>
      <p class="text-[11px] text-center text-slate-400 leading-relaxed">Quantity desimal didukung untuk kg (contoh 1.5 kg)<br class="hidden sm:block">Di HP: tap + tahan untuk ubah qty lebih cepat</p>
    </div>
  </div>
</div>

<script>
let laundryTypes = @json($laundryTypes);
let products = @json($products);
let productMap = {}; products.forEach(p=> productMap[p.id]=p);
let cart = [];
let selectedCustomerId = document.getElementById('customerId').value;

function addToCart(id){
  let p = productMap[id];
  if(!p) return;
  let existing = cart.find(c=> c.product_id===id);
  if(existing){
    let step = p.type==='service' ? 0.5 : 1;
    existing.quantity = parseFloat((parseFloat(existing.quantity)+step).toFixed(3));
  } else {
    cart.push({product_id:id, name:p.name, sku:p.sku, unit:p.unit, price:parseFloat(p.price), type:p.type, quantity:1, discount:0});
  }
  renderCart();
  if(window.navigator.vibrate) navigator.vibrate(20);
}
function renderCart(){
  let wrap = document.getElementById('cartItems');
  if(cart.length===0){ wrap.innerHTML='<div id="emptyCart" class="text-center py-8 text-slate-400 text-sm">Keranjang kosong<br><span class="text-xs">Tap produk untuk menambah</span></div>'; calc(); return; }
  let html='';
  cart.forEach((c,i)=>{
    html+= `<div class="flex gap-2 items-center border border-slate-200 rounded-xl p-2.5 bg-white">
      <div class="flex-1 min-w-0"><div class="font-medium text-[13px] sm:text-sm leading-tight truncate">${c.name}</div><div class="text-[11px] text-slate-500 truncate">${c.sku} • ${money(c.price)}/${c.unit}</div></div>
      <div class="flex items-center gap-1 shrink-0">
        <button onclick="changeQty(${i},-1)" class="w-8 h-8 grid place-items-center rounded-lg bg-slate-100 active:bg-slate-200 text-slate-700">−</button>
        <input type="number" value="${c.quantity}" step="${c.type==='service'?0.1:1}" min="0.001" onchange="updQty(${i},this.value)" class="w-14 sm:w-16 border border-slate-200 rounded-lg px-1 py-1.5 text-center text-sm">
        <button onclick="changeQty(${i},1)" class="w-8 h-8 grid place-items-center rounded-lg bg-slate-100 active:bg-slate-200 text-slate-700">+</button>
      </div>
      <div class="hidden sm:block font-semibold text-sm w-20 text-right shrink-0">${money(c.quantity*c.price - c.discount)}</div>
      <button onclick="removeItem(${i})" class="shrink-0 w-8 h-8 grid place-items-center rounded-lg hover:bg-rose-50 text-rose-400">×</button>
    </div>`;
  });
  wrap.innerHTML=html;
  calc();
}
function changeQty(i,delta){
  let c=cart[i]; let v=parseFloat((parseFloat(c.quantity)+delta*(c.type==='service'?0.5:1)).toFixed(3));
  if(v<=0) return removeItem(i);
  if(c.type==='product' && !Number.isInteger(v)) v=Math.round(v);
  c.quantity=v; renderCart();
}
function updQty(i,val){
  let v=parseFloat(val); let c=cart[i];
  if(c.type==='product' && !Number.isInteger(v)){ alert('Produk pcs harus bulat'); v=Math.floor(v)||1; }
  if(!v || v<=0) v= c.type==='service'?0.5:1;
  c.quantity=v; renderCart();
}
function removeItem(i){ cart.splice(i,1); renderCart(); }
function money(n){ return 'Rp '+Number(n).toLocaleString('id-ID'); }
function calc(){
  let sub= cart.reduce((s,c)=> s + c.quantity*c.price - c.discount, 0);
  let disc= parseFloat(document.getElementById('discount').value)||0;
  let discType=document.getElementById('discountType').value;
  let tax= parseFloat(document.getElementById('tax').value)||0;
  if(discType==='percent') disc = sub*disc/100;
  if(disc>sub) disc=sub;
  let total = Math.max(0, sub - disc + tax);
  let paid = parseFloat(document.getElementById('paidAmount').value)||0;
  let change = paid>total ? paid-total : 0;
  document.getElementById('subtotal').textContent=money(sub);
  document.getElementById('grandTotal').textContent=money(total);
  document.getElementById('change').textContent=money(change);
  return {sub,disc,tax,total,paid,change};
}
['discount','discountType','tax','paidAmount'].forEach(id=> document.getElementById(id).addEventListener('input', calc));
function filterCat(cat){
  document.querySelectorAll('.catBtn').forEach(b=> b.className = b.dataset.cat===cat ? 'catBtn snap-start shrink-0 px-3.5 py-2 rounded-full bg-slate-900 text-white text-sm font-medium' : 'catBtn snap-start shrink-0 px-3.5 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium whitespace-nowrap');
  document.querySelectorAll('.productCard').forEach(el=>{ el.style.display = (!cat || el.dataset.cat===cat) ? '' : 'none'; });
}
// init cat buttons dataset
document.querySelectorAll('.catBtn').forEach(b=> b.dataset.cat = b.getAttribute('onclick')?.match(/'([^']*)'/)?.[1] ?? '');
filterCat('');
document.getElementById('productSearch').addEventListener('input', function(){
  let q=this.value.toLowerCase().trim();
  document.querySelectorAll('.productCard').forEach(el=>{
    let match = !q || el.dataset.name.includes(q) || el.dataset.sku.includes(q) || el.dataset.barcode.includes(q);
    el.style.display = match ? '' : 'none';
  });
  if(q){
    fetch('/products-search?q='+encodeURIComponent(q)).then(r=>r.json()).then(data=>{
      if(data.length===1 && (data[0].sku.toLowerCase()===q || (data[0].barcode && data[0].barcode.toLowerCase()===q))){
        addToCart(data[0].id);
        document.getElementById('productSearch').value='';
        document.querySelectorAll('.productCard').forEach(el=> el.style.display='');
      }
    });
  }
});
let cTimer;
document.getElementById('customerSearch').addEventListener('input', function(){
  let q=this.value.trim(); let box=document.getElementById('customerResults');
  if(!q){ box.classList.add('hidden'); return; }
  clearTimeout(cTimer);
  cTimer=setTimeout(()=>{
    fetch('/customers-search?q='+encodeURIComponent(q)).then(r=>r.json()).then(data=>{
      if(!data.length){ box.innerHTML='<div class="p-3 text-sm text-slate-400">Tidak ditemukan</div>'; box.classList.remove('hidden'); return; }
      box.innerHTML = data.map(c=> `<button onclick="selectCustomer(${c.id},'${c.name.replace(/'/g,"\'")}','${c.phone}')" class="w-full text-left px-3 py-2.5 hover:bg-slate-50 text-sm border-b last:border-0"><b>${c.name}</b><br><span class="text-slate-500 text-xs">${c.phone} • ${c.code}</span></button>`).join('');
      box.classList.remove('hidden');
    });
  },250);
});
function selectCustomer(id,name,phone){
  selectedCustomerId=id; document.getElementById('customerId').value=id;
  document.getElementById('custName').textContent=name; document.getElementById('custPhone').textContent=phone;
  document.getElementById('customerResults').classList.add('hidden');
  document.getElementById('customerSearch').value='';
}
function clearCustomer(){
  let walkId = '{{ ($customers->firstWhere('phone','000000')->id ?? ($customers->first()->id ?? '')) }}';
  selectCustomer(walkId,'Walk-in Customer','000000');
}
// --- Rincian Laundry: collapsed default, kosong default, dropdown tambah ---
let selectedLaundry = new Set();

function toggleLaundry(){
  let p=document.getElementById('laundryPanel'), c=document.getElementById('laundryChevron');
  let isHidden = p.classList.contains('hidden');
  if(isHidden){ p.classList.remove('hidden'); c.style.transform='rotate(0deg)'; }
  else { p.classList.add('hidden'); c.style.transform='rotate(-90deg)'; }
}

function refreshLaundrySelect(){
  let sel=document.getElementById('laundrySelect');
  if(!sel) return;
  let keep = sel.value;
  sel.innerHTML='<option value="">— Pilih jenis —</option>';
  laundryTypes.forEach(t=>{
    if(selectedLaundry.has(t.code)) return;
    let opt=document.createElement('option');
    opt.value=t.code; opt.dataset.name=t.name; opt.dataset.icon=t.icon||'📦';
    opt.textContent=(t.icon||'📦')+' '+t.name;
    sel.appendChild(opt);
  });
  if(keep && !selectedLaundry.has(keep)) sel.value=keep;
}

function createLaundryCard(code, name, icon){
  if(selectedLaundry.has(code)){
    let el=document.getElementById('laundry_'+code);
    if(el) el.focus();
    return;
  }
  selectedLaundry.add(code);
  let grid=document.getElementById('laundryGrid');
  let wrap=document.createElement('div');
  wrap.id='wrap_'+code;
  wrap.className='bg-white border border-amber-200 rounded-xl px-3 py-2.5 flex items-center gap-3 min-w-0';
  wrap.innerHTML=`
    <div class="flex items-center gap-2.5 min-w-0 flex-1">
      <span class="w-9 h-9 sm:w-8 sm:h-8 grid place-items-center rounded-xl bg-amber-100 text-sm shrink-0">`+(icon||'📦')+`</span>
      <span class="text-sm font-semibold text-slate-800 truncate">`+name+`</span>
    </div>
    <div class="flex items-center gap-1.5 shrink-0">
      <button type="button" onclick="stepLaundry('`+code+`',-1)" class="w-9 h-9 sm:w-8 sm:h-8 grid place-items-center rounded-xl bg-slate-100 active:bg-slate-200 text-slate-700 font-bold">−</button>
      <input id="laundry_`+code+`" data-code="`+code+`" type="number" min="0" inputmode="numeric" placeholder="0" class="laundryInput w-16 sm:w-20 border border-slate-200 rounded-xl px-2 py-2.5 text-center text-sm font-semibold focus:ring-2 focus:ring-amber-400 outline-none">
      <button type="button" onclick="stepLaundry('`+code+`',1)" class="w-9 h-9 sm:w-8 sm:h-8 grid place-items-center rounded-xl bg-slate-900 active:bg-black text-white font-bold">+</button>
      <span class="text-xs text-slate-500 w-7 text-center hidden sm:inline">pcs</span>
      <button type="button" onclick="removeLaundryRow('`+code+`')" class="ml-1 w-8 h-8 grid place-items-center rounded-full hover:bg-rose-50 active:bg-rose-100 text-slate-400 hover:text-rose-600" title="Hapus baris">×</button>
    </div>`;
  grid.appendChild(wrap);
  wrap.querySelector('.laundryInput').addEventListener('input', updateLaundryTotal);
  refreshLaundrySelect();
  updateLaundryTotal();
  wrap.querySelector('.laundryInput').focus();
}

function addLaundryFromSelect(){
  let sel=document.getElementById('laundrySelect');
  let code=(sel.value||'').trim();
  if(!code){ alert('Pilih jenis dulu'); return; }
  let t=laundryTypes.find(x=> x.code===code);
  if(!t) return;
  createLaundryCard(t.code, t.name, t.icon||'📦');
  sel.value='';
}

function stepLaundry(code,delta){
  let el=document.getElementById('laundry_'+code);
  if(!el) return;
  let v=(parseInt(el.value||'0',10)||0)+delta;
  if(v<0) v=0;
  el.value = v? String(v): '';
  updateLaundryTotal();
}
function removeLaundryRow(code){
  let el=document.getElementById('wrap_'+code);
  if(el) el.remove();
  selectedLaundry.delete(code);
  refreshLaundrySelect();
  updateLaundryTotal();
}

async function createLaundryType(){
  let nameEl=document.getElementById('newLaundryName');
  let iconEl=document.getElementById('newLaundryIcon');
  let name=(nameEl.value||'').trim();
  if(!name){ alert('Isi nama jenis'); return; }
  let icon=(iconEl.value||'').trim() || '📦';
  let res=await fetch('{{ route('api.laundry-types.store') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify({name,icon})});
  let data=await res.json();
  if(!res.ok){ alert(data.message||'Gagal buat jenis'); return; }
  laundryTypes.push(data);
  laundryTypes.sort((a,b)=> (a.sort_order||0)-(b.sort_order||0));
  nameEl.value=''; iconEl.value='';
  refreshLaundrySelect();
  createLaundryCard(data.code, data.name, data.icon||'📦');
}

function getLaundryDetails(){
  let d={};
  document.querySelectorAll('.laundryInput').forEach(el=>{
    let code=el.dataset.code;
    let v=parseInt(el.value||'0',10);
    if(v>0) d[code]=v;
  });
  let desc=(document.getElementById('laundry_lainnya_desc')?.value||'').trim();
  let catatan=(document.getElementById('laundry_catatan')?.value||'').trim();
  if(desc) d['lainnya_desc']=desc;
  if(catatan) d['catatan']=catatan;
  return Object.keys(d).length? d : null;
}
function setLaundryStatus(v){
  document.getElementById('orderStatus').value=v;
  let a=document.getElementById('btnBaru'), b=document.getElementById('btnSelesai');
  if(v==='received'){ a.className='py-3 rounded-xl text-sm font-semibold border-2 border-slate-900 bg-slate-900 text-white'; b.className='py-3 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'; }
  else { b.className='py-3 rounded-xl text-sm font-semibold border-2 border-emerald-600 bg-emerald-600 text-white'; a.className='py-3 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'; }
}
function clearLaundry(){
  document.getElementById('laundryGrid').innerHTML='';
  selectedLaundry.clear();
  ['laundry_lainnya_desc','laundry_catatan'].forEach(id=>{
    let el=document.getElementById(id); if(el) el.value='';
  });
  refreshLaundrySelect();
  updateLaundryTotal();
}
function updateLaundryTotal(){
  let tot=0;
  document.querySelectorAll('.laundryInput').forEach(el=>{ tot += parseInt(el.value||'0',10)||0; });
  let totalEl=document.getElementById('laundryTotal'); if(totalEl) totalEl.textContent=tot;
  let badge=document.getElementById('laundryBadge');
  let summary=document.getElementById('laundrySummaryText');
  let empty=document.getElementById('laundryEmpty');
  let grid=document.getElementById('laundryGrid');
  if(badge){ badge.textContent=tot+' pcs'; badge.classList.toggle('hidden', tot===0 && selectedLaundry.size===0); }
  if(empty && grid){ empty.classList.toggle('hidden', selectedLaundry.size>0); }
  if(summary){
    if(tot>0 || selectedLaundry.size>0){
      let parts=[];
      document.querySelectorAll('.laundryInput').forEach(el=>{
        let v=parseInt(el.value||'0',10);
        if(v>0) parts.push(el.dataset.code+':'+v);
      });
      summary.textContent = parts.length ? parts.join(' • ') : selectedLaundry.size+' jenis';
      summary.classList.remove('hidden');
    } else {
      summary.textContent='';
      summary.classList.add('hidden');
    }
  }
}


async function checkout(){
  if(cart.length===0){ alert('Keranjang kosong'); return; }
  let {total,paid}=calc();
  let discType=document.getElementById('discountType').value;
  let discInput=parseFloat(document.getElementById('discount').value)||0;
  let payload={
    customer_id: selectedCustomerId||null,
    items: cart.map(c=> ({product_id:c.product_id, quantity:c.quantity, price:c.price, discount:c.discount})),
    discount: discType==='percent'? discInput : (parseFloat(document.getElementById('discount').value)||0),
    discount_type: discType,
    tax: parseFloat(document.getElementById('tax').value)||0,
    paid_amount: paid,
    payment_method: document.getElementById('paymentMethod').value,
    order_status: document.getElementById('orderStatus').value || 'received',
    laundry_details: getLaundryDetails(),
    _token: '{{ csrf_token() }}'
  };
  let btn=document.getElementById('payBtn'); btn.disabled=true; btn.textContent='Memproses...';
  try{
    let res= await fetch('{{ route('pos.store') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: JSON.stringify(payload)});
    let data= await res.json();
    if(!res.ok){ alert(data.message || JSON.stringify(data.errors || data)); btn.disabled=false; btn.textContent='BAYAR & CETAK'; return; }
    window.location.href='/orders/'+data.id+'?fresh=1';
  }catch(e){ alert(e.message); btn.disabled=false; btn.textContent='BAYAR & CETAK'; }
}
document.addEventListener('click', e=>{
  let box=document.getElementById('customerResults');
  if(!e.target.closest('#customerSearch') && !e.target.closest('#customerResults')) box.classList.add('hidden');
});
</script>
@endsection
