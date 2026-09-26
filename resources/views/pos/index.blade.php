@extends('layouts.app')
@section('title','POS Kasir')
@push('head')
<style>
  /* Flat UI POS overrides */
  .pos-flat-icon{width:1.1em;height:1.1em;display:inline-block;vertical-align:-0.18em;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .pos-search-icon{position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:#57503f;width:1.1rem;height:1.1rem}
  .pos-cat-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem .9rem;border-radius:9999px;font-size:.85rem;font-weight:600;line-height:1;border:1px solid transparent;white-space:nowrap;flex-shrink:0;transition:all .15s ease}
  .pos-cat-pill.is-active{background:#2b2320;color:#fff;border-color:#2b2320}
  .pos-cat-pill:not(.is-active){background:#f4efe6;color:#57503f;border-color:#e7e2d9}
  .pos-cat-pill:not(.is-active):hover{background:#e7e2d9}
  .pos-product-card{text-align:left;background:#fff;border:1px solid #e7e2d9;border-radius:1rem;padding:.75rem;display:flex;flex-direction:column;gap:.4rem;transition:all .15s ease;min-height:0}
  .pos-product-card:hover{border-color:#5eead4;box-shadow:0 1px 2px rgba(15,23,42,.06)}
  .pos-product-card:active{transform:scale(.98)}
  .pos-product-card .pc-sku{font-size:.7rem;font-family:ui-monospace,SFMono-Regular,monospace;background:#f4efe6;padding:.2rem .5rem;border-radius:9999px;color:#6b6357;max-width:70%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .pos-product-card .pc-unit{font-size:.7rem;padding:.2rem .5rem;border-radius:9999px;font-weight:600}
  .pos-product-card .pc-name{font-size:.82rem;font-weight:600;line-height:1.2;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.4em;color:#2b2320}
  .pos-product-card .pc-cat{font-size:.7rem;color:#6b6357;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .pos-product-card .pc-price{margin-top:auto;font-size:.85rem;font-weight:700;color:#0f766e}
  .pos-action-bar{position:sticky;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #e7e2d9;padding:.65rem .75rem;display:grid;grid-template-columns:1fr 1fr;gap:.5rem;z-index:10}
  .pos-action-bar .btn{padding:.7rem .5rem;border-radius:.85rem;font-weight:600;font-size:.85rem;display:inline-flex;align-items:center;justify-content:center;gap:.4rem;border:0;cursor:pointer;transition:all .15s ease}
  .pos-action-bar .btn:active{transform:scale(.98)}
  .pos-action-bar .btn-primary{background:#0f766e;color:#fff;box-shadow:0 4px 12px rgba(15,118,110,.25)}
  .pos-action-bar .btn-secondary{background:#f4efe6;color:#57503f}
  .pos-action-bar .btn-ghost{background:#fff;color:#6b6357;border:1px solid #e7e2d9}
  .pos-action-bar .btn-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
  .pos-action-bar .btn-success{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0}
  /* Cart item: target sentuh besar untuk kasir yang sambil berdiri.
     Baris ditata dua tingkat: nama + harga di atas, kontrol di bawah.
     Kontrol penuh lebar lebih mudah ditebak dan diteken daripada ikon kecil. */
  .pos-cart-item{display:flex;flex-direction:column;gap:.55rem;background:#fff;border:1px solid #e7e2d9;border-radius:.5rem;padding:.7rem .75rem}
  .pos-cart-item:hover{border-color:#d4ccbf}
  .pos-cart-item .ci-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem}
  .pos-cart-item .ci-name{font-size:.85rem;font-weight:600;line-height:1.25;color:#2b2320;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .pos-cart-item .ci-meta{font-size:.7rem;color:#6b6357;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;margin-top:.1rem}
  .pos-cart-item .ci-subtotal{font-size:.9rem;font-weight:700;color:#2b2320;flex-shrink:0;white-space:nowrap}
  /* Baris kontrol: tombol lebar penuh, bukan ikon 30px yang mudah salah ketuk */
  .pos-cart-item .ci-bottom{display:flex;align-items:center;justify-content:space-between;gap:.5rem}
  .pos-cart-item .ci-qty{display:flex;align-items:center;gap:.35rem;flex-shrink:0}
  .pos-cart-item .ci-qty button{width:2.5rem;height:2.5rem;border:1px solid #d4ccbf;border-radius:.4rem;background:#faf7f1;color:#3b352e;font-weight:700;font-size:1.1rem;cursor:pointer;display:grid;place-items:center;line-height:1}
  .pos-cart-item .ci-qty button:hover{background:#f4efe6;border-color:#a89e8f}
  .pos-cart-item .ci-qty button:active{background:#e7e2d9;transform:scale(.96)}
  .pos-cart-item .ci-qty input{width:3.5rem;height:2.5rem;border:1px solid #d4ccbf;border-radius:.4rem;padding:0 .25rem;text-align:center;font-size:1rem;font-weight:700;background:#fff;color:#2b2320}
  .pos-cart-item .ci-qty input:focus{border-color:#0f766e;outline:2px solid #ccfbf1;outline-offset:0}
  .pos-cart-item .ci-remove{height:2.5rem;padding:0 .85rem;border:1px solid #e7e2d9;border-radius:.4rem;background:#fff;color:#57503f;font-size:.8rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.3rem;flex-shrink:0}
  .pos-cart-item .ci-remove:hover{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
  .pos-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);z-index:80;display:none;align-items:flex-end;justify-content:center;padding:0;animation:posFadeIn .15s ease}
  .pos-modal-backdrop.is-open{display:flex}
  .pos-modal-backdrop.is-center{align-items:center;padding:1rem}
  @media(min-width:640px){.pos-modal-backdrop{align-items:center;padding:1rem}}
  /* Mobile cart drawer: hidden bottom-sheet, slide up saat .cart-open */
  @media(max-width:1023.5px){
    #cartDrawer{position:fixed;left:0;right:0;bottom:0;top:auto;width:100%;max-width:none;height:82vh;max-height:82vh;z-index:60;border-radius:1.25rem 1.25rem 0 0;box-shadow:0 -8px 30px rgba(15,23,42,.18);transform:translateY(105%);transition:transform .28s cubic-bezier(.22,1,.36,1);overflow-y:auto}
    #cartDrawer.cart-open{transform:translateY(0)}
    #cartDrawerOverlay{position:fixed;inset:0;background:rgba(15,23,42,.5);backdrop-filter:blur(2px);z-index:55;display:none}
    #cartDrawerOverlay.show{display:block}
    #cartFab{position:fixed;right:1rem;bottom:1rem;z-index:50;display:flex;align-items:center;gap:.5rem;background:#0f766e;color:#fff;border:0;border-radius:9999px;padding:.8rem 1.1rem;font-weight:700;font-size:.9rem;box-shadow:0 8px 20px rgba(15,118,110,.4);cursor:pointer}
    #cartFab.hidden{display:none}
    #cartFab:active{transform:scale(.96)}
    #cartFab .fab-count{background:#fff;color:#0f766e;font-size:.75rem;font-weight:800;min-width:1.5rem;height:1.5rem;border-radius:9999px;display:grid;place-items:center;padding:0 .35rem}
    body.cart-open{overflow:hidden}
  }
  @media(min-width:1024px){#cartFab,#cartDrawerOverlay{display:none!important} #cartDrawer{position:relative!important;transform:none!important;width:400px!important;max-width:420px!important;height:auto!important;max-height:none!important;border-radius:1rem!important;box-shadow:none!important;z-index:auto!important;overflow:hidden!important}}
  .pos-modal{background:#fff;width:100%;max-width:480px;max-height:90vh;border-radius:1.25rem 1.25rem 0 0;display:flex;flex-direction:column;overflow:hidden;animation:posSlideUp .25s cubic-bezier(.22,1,.36,1)}
  @media(min-width:640px){.pos-modal{border-radius:1.25rem}}
  .pos-modal-header{padding:1rem 1.1rem .75rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f4efe6;flex-shrink:0}
  .pos-modal-header h3{font-weight:700;font-size:1rem;color:#2b2320;margin:0;display:flex;align-items:center;gap:.5rem}
  .pos-modal-header .close-btn{width:2rem;height:2rem;border:0;background:#f4efe6;border-radius:.6rem;color:#6b6357;cursor:pointer;display:grid;place-items:center}
  .pos-modal-body{padding:1rem 1.1rem;overflow-y:auto;flex:1}
  .pos-modal-footer{padding:.75rem 1.1rem;border-top:1px solid #f4efe6;display:grid;grid-template-columns:1fr 1fr;gap:.5rem;flex-shrink:0;background:#faf7f1}
  .pos-modal-footer.single{grid-template-columns:1fr}
  .pos-modal-footer .btn{padding:.75rem;border-radius:.75rem;font-weight:600;font-size:.9rem;border:0;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:.4rem}
  .pos-modal-footer .btn-primary{background:#0f766e;color:#fff}
  .pos-modal-footer .btn-secondary{background:#f4efe6;color:#57503f}
  .pos-modal-footer .btn-ghost{background:#fff;color:#6b6357;border:1px solid #e7e2d9}
  @keyframes posFadeIn{from{opacity:0}to{opacity:1}}
  @keyframes posSlideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
  .pos-summary-row{display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;font-size:.9rem}
  .pos-summary-row .label{color:#6b6357}
  .pos-summary-row .value{font-weight:600;color:#2b2320}
  .pos-summary-row.total{border-top:1px solid #e7e2d9;padding-top:.65rem;margin-top:.4rem;font-size:1.05rem;font-weight:700}
  .pos-summary-row.total .value{color:#0f766e;font-size:1.2rem}
  .pos-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .7rem;border-radius:9999px;font-size:.78rem;font-weight:600}
  .pos-chip-emerald{background:#ecfdf5;color:#047857}
  .pos-chip-amber{background:#fffbeb;color:#b45309}
  .pos-chip-slate{background:#f4efe6;color:#57503f}
  .pos-quick-cash{display:grid;grid-template-columns:repeat(4,1fr);gap:.4rem;margin-top:.4rem}
  .pos-quick-cash button{padding:.55rem .25rem;border-radius:.65rem;background:#f4efe6;color:#57503f;border:1px solid #e7e2d9;font-weight:600;font-size:.78rem;cursor:pointer}
  .pos-quick-cash button:hover{background:#e7e2d9}
  .pos-laundry-card{background:#fff;border:1px solid #fde68a;border-radius:.85rem;padding:.6rem .75rem;display:flex;align-items:center;gap:.6rem;min-width:0}
  .pos-laundry-card .ll-icon{width:2.1rem;height:2.1rem;display:grid;place-items:center;background:#fef3c7;border-radius:.6rem;font-size:1rem;flex-shrink:0}
  .pos-laundry-card .ll-name{font-size:.85rem;font-weight:600;color:#2b2320;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .pos-laundry-card .ll-step{width:1.85rem;height:1.85rem;border:0;border-radius:.55rem;font-weight:700;cursor:pointer;display:grid;place-items:center}
  .pos-laundry-card .ll-step.minus{background:#f4efe6;color:#57503f}
  .pos-laundry-card .ll-step.plus{background:#2b2320;color:#fff}
  .pos-laundry-card input.ll-input{width:3.5rem;border:1px solid #e7e2d9;border-radius:.55rem;padding:.3rem;text-align:center;font-weight:600;font-size:.85rem;background:#fff}
  .pos-laundry-card .ll-remove{width:1.85rem;height:1.85rem;border:0;background:transparent;color:#57503f;border-radius:.55rem;cursor:pointer;display:grid;place-items:center}
  .pos-laundry-card .ll-remove:hover{background:#fef2f2;color:#dc2626}
  .pos-pay-method-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem;margin-top:.4rem}
  .pos-pay-method-grid button{padding:.65rem .25rem;border-radius:.7rem;background:#f4efe6;color:#57503f;border:1px solid #e7e2d9;font-weight:600;font-size:.78rem;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:.25rem}
  .pos-pay-method-grid button.is-active{background:#f0fdfa;color:#115e59;border-color:#5eead4}
  .pos-pay-method-grid button:hover:not(.is-active){background:#e7e2d9}
  /* Empty cart friendly */
  .pos-empty{padding:1.5rem .75rem;text-align:center;color:#6b6357}
  .pos-empty .empty-icon{width:3rem;height:3rem;margin:0 auto .5rem;opacity:.5}
  /* Section card */
  .pos-section{background:#fff;border:1px solid #e7e2d9;border-radius:1.1rem;padding:.85rem 1rem}
  .pos-section-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b6357;margin-bottom:.5rem;display:flex;align-items:center;justify-content:space-between}
  /* Hide number input spinners */
  input[type=number]::-webkit-outer-spin-button,input[type=number]::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
  input[type=number]{-moz-appearance:textfield}
  /* Status buttons */
  .pos-status-btn{padding:.65rem .5rem;border-radius:.75rem;font-size:.85rem;font-weight:600;border:1px solid #e7e2d9;background:#fff;color:#57503f;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:.35rem;flex:1}
  .pos-status-btn.is-active.received{background:#fef3c7;color:#92400e;border-color:#fbbf24}
  .pos-status-btn.is-active.ready{background:#d1fae5;color:#065f46;border-color:#10b981}
  /* Scroll lock helper */
  body.pos-modal-open{overflow:hidden}
</style>
@endpush
@section('content')
@if(isset($order))
<div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex flex-wrap items-center justify-between gap-2 mb-3">
  <div class="text-sm"><span class="text-amber-800 font-semibold">✏️ Mode Edit</span> <span class="font-mono font-bold">{{ $order->order_number }}</span> <span class="text-paper-500">• {{ $order->customer->name ?? 'Walk-in' }} • {{ $order->order_status }}</span></div>
  <div class="flex gap-2"><a href="{{ route('orders.show',$order) }}" class="px-3 py-1.5 bg-white border rounded-full text-xs font-semibold">← Kembali</a><a href="{{ route('pos.index') }}" class="px-3 py-1.5 bg-paper-900 text-white rounded-full text-xs font-semibold">POS Baru</a></div>
</div>
@endif
<div class="flex flex-col lg:flex-row gap-3 lg:gap-4 lg:h-[calc(100vh-88px)]">
  {{-- Produk kiri --}}
  <div class="flex-1 bg-white rounded-2xl border flex flex-col overflow-hidden min-h-[42vh] lg:min-h-0">
    <div class="p-3 sm:p-4 border-b space-y-3">
      <div class="relative">
        <input id="productSearch" type="text" inputmode="search" placeholder="Cari produk / SKU / scan barcode" class="w-full border border-paper-300 rounded-xl sm:rounded-2xl pl-10 pr-4 py-3 sm:py-3 text-[15px] focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none" autofocus>
        <svg class="pos-search-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      </div>
      <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 -mx-1 px-1 snap-x">
        <button onclick="filterCat('')" data-cat="" class="pos-cat-pill is-active snap-start">Semua</button>
        @foreach(\App\Models\Category::all() as $c)<button onclick="filterCat('{{ $c->id }}')" data-cat="{{ $c->id }}" class="pos-cat-pill snap-start">{{ $c->name }}</button>@endforeach
      </div>
    </div>
    <div id="productGrid" class="flex-1 overflow-y-auto p-2 sm:p-4 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-3 content-start">
      @foreach($products as $p)
      <button onclick="addToCart({{ $p->id }})" data-cat="{{ $p->category_id }}" data-name="{{ strtolower($p->name) }}" data-sku="{{ strtolower($p->sku) }}" data-barcode="{{ strtolower($p->barcode ?? '') }}" class="pos-product-card productCard">
        <div class="flex items-start justify-between gap-1">
          <span class="pc-sku">{{ $p->sku }}</span>
          <span class="pc-unit {{ $p->type=='service'?'bg-teal-100 text-teal-700':'bg-emerald-100 text-emerald-700' }}">{{ $p->unit }}</span>
        </div>
        <div class="pc-name">{{ $p->name }}</div>
        <div class="pc-cat">{{ $p->category->name }}</div>
        <div class="pc-price">{{ money($p->price) }}</div>
      </button>
      @endforeach
    </div>
    <div class="lg:hidden px-3 py-2 border-t bg-paper-100 text-xs text-paper-500 text-center">Tap produk untuk tambah ke keranjang</div>
  </div>

  {{-- Keranjang kanan: di desktop fixed width, di mobile jadi bottom-sheet drawer (#cartDrawer) --}}
  <div id="cartDrawer" class="w-full lg:w-[400px] xl:w-[420px] bg-white rounded-2xl border flex flex-col overflow-hidden shrink-0">
    <div class="p-3 sm:p-4 border-b space-y-3">
      <div class="flex items-baseline justify-between gap-2">
        <label for="customerSearch" class="text-xs font-semibold text-paper-600">Pilih customer</label>
        <span class="text-[11px] text-paper-500">Wajib diisi sebelum bayar</span>
      </div>
      <div class="flex gap-2">
        <div class="flex-1 relative min-w-0">
          <input id="customerSearch" type="text" placeholder="Ketik nama atau nomor HP" required
                 class="w-full border border-paper-300 rounded-lg px-3 py-3 text-sm focus:ring-2 focus:ring-teal-500 outline-none">
          <div id="customerResults" class="absolute z-10 w-full bg-white border border-paper-300 rounded-lg shadow-lg mt-1.5 hidden max-h-52 overflow-y-auto"></div>
        </div>
        <button type="button" onclick="openNewCustomerModal()" class="shrink-0 px-3 sm:px-4 py-3 bg-paper-900 text-white rounded-lg text-sm font-semibold flex items-center gap-1">
          <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
          Customer baru
        </button>
      </div>
      <div id="selectedCustomer" class="bg-rose-50 border border-rose-200 rounded-lg px-3 py-2.5 text-sm flex justify-between items-center gap-2">
        <span class="min-w-0 truncate">
          <b id="custName" class="text-rose-800">Belum ada customer dipilih</b>
          <span id="custPhone" class="text-rose-700"></span>
        </span>
        <button type="button" onclick="clearCustomer()" class="shrink-0 w-7 h-7 grid place-items-center rounded-full hover:bg-white text-rose-600 hidden" aria-label="Hapus pilihan customer">
          <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </div>
      <p id="customerRequiredHint" class="text-xs text-rose-700 hidden">Pilih customer dulu sebelum membayar.</p>
      <input type="hidden" id="customerId" value="">
    </div>

    {{-- Rincian Laundry — dibuka via modal popup --}}
    <div class="mx-3 sm:mx-4 mb-3">
      <button type="button" onclick="openLaundryModal()" class="w-full flex items-center justify-between px-3 sm:px-4 py-3 text-left bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100/60 transition">
        <span class="text-xs sm:text-sm font-bold text-amber-900 flex items-center gap-2">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#b45309"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
          Rincian Laundry <span id="laundryBadge" class="hidden bg-amber-900 text-amber-50 text-[10px] px-2 py-0.5 rounded-full">0 pcs</span>
        </span>
        <span class="flex items-center gap-2">
          <span id="laundrySummaryText" class="hidden sm:inline text-[11px] text-amber-700/70 truncate max-w-[18ch]"></span>
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#b45309;width:1.2rem;height:1.2rem"><path d="m6 9 6 6 6-6"/></svg>
        </span>
      </button>
      <p class="text-[11px] leading-relaxed text-amber-800/80 mt-1">Isi jumlah per jenis, misalnya Baju 3 pcs. Biarkan kosong kalau order ini bukan laundry. <a href="#" onclick="event.preventDefault();openLaundryTypesModal()" class="underline font-semibold">Kelola jenis</a></p>
    </div>

    <div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-2 sm:space-y-3 min-h-[18vh] lg:min-h-0" id="cartItems">
      <div id="emptyCart" class="pos-empty">
        <svg class="empty-icon pos-flat-icon" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
        <p class="text-sm">Keranjang kosong</p>
        <p class="text-xs">Tap produk di atas untuk menambah</p>
      </div>
    </div>

    <div class="border-t p-3 sm:p-4 space-y-3 bg-paper-100">
      <div class="space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-paper-500">Subtotal</span><span id="subtotal" class="font-medium">Rp 0</span></div>

        {{-- Diskon dan pajak disembunyikan di balik satu tombol. Untuk transaksi
             biasa (kebanyakan orders) tidak ada diskon, jadi field kosong hanya
             menambah beban visual dan membuat pemula ragu apakah harus diisi.
             Yang baru dipakai tetap kelihatan, jadi kasir tahu itu bukan opsional. --}}
        <div id="optionalToggleRow" class="hidden">
          <button type="button" id="optionalToggle" onclick="toggleOptionalFields()"
                  class="text-sm text-teal-700 hover:underline flex items-center gap-1.5 py-1">
            <span id="optionalToggleIcon" style="display:inline-block;transition:transform .15s ease">&#9656;</span>
            <span id="optionalToggleText">Tambah diskon atau pajak</span>
          </button>
        </div>

        <div id="optionalFields" class="hidden space-y-2 pt-1">
          <div class="flex justify-between items-center gap-2">
            <label for="discount" class="text-paper-500 shrink-0">Diskon</label>
            <div class="flex items-center gap-1.5">
              <input id="discount" type="number" value="0" min="0" inputmode="numeric" class="w-20 sm:w-24 border border-paper-300 rounded-lg px-2.5 py-2 text-right text-sm focus:ring-2 focus:ring-teal-500 outline-none">
              <select id="discountType" class="border border-paper-300 rounded-lg px-2 py-2 text-xs bg-white"><option value="fixed">Rp</option><option value="percent">%</option></select>
            </div>
          </div>
          <div class="flex justify-between items-center gap-2">
            <label for="tax" class="text-paper-500 shrink-0">Pajak</label>
            <input id="tax" type="number" value="0" min="0" inputmode="numeric" class="w-24 sm:w-28 border border-paper-300 rounded-lg px-2.5 py-2 text-right text-sm focus:ring-2 focus:ring-teal-500 outline-none">
          </div>
        </div>

        <div id="discountLine" class="hidden flex justify-between text-sm">
          <span class="text-paper-500">Diskon</span>
          <span id="discountLineValue" class="font-medium text-rose-700">-Rp 0</span>
        </div>
        <div id="taxLine" class="hidden flex justify-between text-sm">
          <span class="text-paper-500">Pajak</span>
          <span id="taxLineValue" class="font-medium">Rp 0</span>
        </div>

        <div class="flex justify-between items-baseline font-bold text-lg pt-2 border-t border-paper-300">
          <span>Total</span>
          <span id="grandTotal" class="text-teal-700 text-xl">Rp 0</span>
        </div>
      </div>

      <div class="space-y-2">
        <div class="flex items-baseline justify-between gap-2">
          <label class="text-xs font-semibold text-paper-600">Status cucian</label>
          <span class="text-[11px] text-paper-500">Pilih saat laundry sudah selesai</span>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <button type="button" id="btnBaru" onclick="setLaundryStatus('received')" class="pos-status-btn is-active received">
            <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#b45309"><circle cx="12" cy="12" r="9" fill="currentColor"/></svg>
            Masih dicuci
          </button>
          <button type="button" id="btnSelesai" onclick="setLaundryStatus('ready')" class="pos-status-btn ready">
            <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#0f766e"><path d="m20 6-11 11-5-5"/></svg>
            Sudah selesai
          </button>
        </div>
        <input type="hidden" id="orderStatus" value="received">
      </div>

      <div class="grid grid-cols-2 gap-2">
        <label for="paymentMethod" class="sr-only">Metode pembayaran</label>
        <select id="paymentMethod" class="border border-paper-300 rounded-lg px-3 py-3 text-sm bg-white focus:ring-2 focus:ring-teal-500 outline-none"><option value="cash">Cash</option><option value="transfer">Transfer</option><option value="qris">QRIS</option><option value="debit">Debit</option><option value="e_wallet">E-Wallet</option></select>
        <label for="paidAmount" class="sr-only">Jumlah uang diterima</label>
        <input id="paidAmount" type="number" inputmode="numeric" placeholder="Uang diterima" class="border border-paper-300 rounded-lg px-3 py-3 text-sm focus:ring-2 focus:ring-teal-500 outline-none">
      </div>

      {{-- Kembalian hanya muncul kalau ada kembalian. "Rp 0" yang selalu
           tampil cuma noise dan membuat kasir wonders apakah sudah benar. --}}
      <div id="changeRow" class="hidden flex justify-between text-sm">
        <span class="text-paper-500">Kembalian</span>
        <span id="change" class="font-semibold text-paper-800">Rp 0</span>
      </div>

      <button onclick="isEditMode ? submitEditOrder() : openCheckoutModal()" id="payBtn"
              class="w-full font-semibold py-4 text-base rounded-lg flex items-center justify-center gap-2
                     bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white transition-colors duration-120
                     focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-600 focus-visible:outline-offset-2"
              :class="isEditMode?'bg-amber-600 hover:bg-amber-700 active:bg-amber-800':''">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/></svg>
        <span id="payBtnText">Lanjut ke pembayaran</span>
      </button>
      <p class="text-[11px] text-paper-500 text-center leading-relaxed">
        Struk dan pilihan cetak muncul di layar berikutnya.
      </p>
    </div>
  </div>
</div>

<button id="cartFab" class="hidden" onclick="toggleCartDrawer()">
  <span class="fab-count" id="cartCount">0</span> Keranjang
</button>
<div id="cartDrawerOverlay" class="hidden"></div>

{{-- ===== POPUP MODALS ===== --}}

{{-- Modal: Checkout (pilih metode bayar & nominal) --}}
<div id="modalCheckout" class="pos-modal-backdrop">
  <div class="pos-modal">
    <div class="pos-modal-header">
      <h3>
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#0f766e"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/></svg>
        Konfirmasi Pembayaran
      </h3>
      <button type="button" onclick="closeModal('modalCheckout')" class="close-btn" aria-label="Tutup">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="pos-modal-body">
      <div id="checkoutSummary"></div>
      <label class="text-xs font-semibold text-paper-600 mt-3 block">Metode Pembayaran</label>
      <div class="pos-pay-method-grid" id="checkoutPayMethods">
        <button type="button" data-method="cash" class="is-active" onclick="selectPayMethod(this,'cash')">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.3rem;height:1.3rem"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
          <span>Cash</span>
        </button>
        <button type="button" data-method="transfer" onclick="selectPayMethod(this,'transfer')">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.3rem;height:1.3rem"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 10 10 5 10-5"/></svg>
          <span>Transfer</span>
        </button>
        <button type="button" data-method="qris" onclick="selectPayMethod(this,'qris')">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.3rem;height:1.3rem"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM18 18h3v3h-3z"/></svg>
          <span>QRIS</span>
        </button>
        <button type="button" data-method="debit" onclick="selectPayMethod(this,'debit')">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.3rem;height:1.3rem"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
          <span>Debit</span>
        </button>
        <button type="button" data-method="e_wallet" onclick="selectPayMethod(this,'e_wallet')">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.3rem;height:1.3rem"><path d="M21 12V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1"/><path d="M16 12h6"/></svg>
          <span>E-Wallet</span>
        </button>
      </div>
      <label class="text-xs font-semibold text-paper-600 mt-3 block">Nominal Bayar</label>
      <input id="modalPaidAmount" type="number" inputmode="numeric" placeholder="0" class="w-full mt-1 border border-paper-300 rounded-xl px-3 py-3 text-base font-semibold focus:ring-2 focus:ring-teal-500 outline-none">
      <div class="pos-quick-cash" id="quickCashButtons">
        <button type="button" onclick="quickCash('exact')">Pas</button>
        <button type="button" data-amt="50000">50K</button>
        <button type="button" data-amt="100000">100K</button>
        <button type="button" data-amt="200000">200K</button>
      </div>
      <div class="pos-summary-row mt-3"><span class="label">Total</span><span class="value" id="modalTotalText">Rp 0</span></div>
      <div class="pos-summary-row"><span class="label">Dibayar</span><span class="value" id="modalPaidText">Rp 0</span></div>
      <div class="pos-summary-row" id="modalChangeRow"><span class="label">Kembalian</span><span class="value text-emerald-600" id="modalChangeText">Rp 0</span></div>
    </div>
    <div class="pos-modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('modalCheckout')">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
        Batal
      </button>
      <button type="button" class="btn btn-primary" id="modalConfirmPayBtn" onclick="confirmCheckout()">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="m20 6-11 11-5-5"/></svg>
        Bayar Sekarang
      </button>
    </div>
  </div>
</div>

{{-- Modal: Struk (aksi: Kirim WA, Cetak, Cetak&WA, Close) --}}
<div id="modalReceipt" class="pos-modal-backdrop is-center">
  <div class="pos-modal" style="max-width:420px">
    <div class="pos-modal-header">
      <h3>
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#10b981"><path d="m20 6-11 11-5-5"/></svg>
        Pembayaran Berhasil
      </h3>
      <button type="button" onclick="closeReceiptModal()" class="close-btn" aria-label="Tutup">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="pos-modal-body">
      <div class="text-center py-2">
        <div class="mx-auto w-14 h-14 rounded-full grid place-items-center mb-2" style="background:#d1fae5">
          <svg viewBox="0 0 24 24" style="width:1.8rem;height:1.8rem;color:#047857;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><path d="m20 6-11 11-5-5"/></svg>
        </div>
        <p class="text-xs text-paper-500" id="receiptOrderNumber">Order #</p>
        <p class="text-2xl font-bold text-paper-800 mt-1" id="receiptTotal">Rp 0</p>
        <p class="text-xs text-paper-500 mt-1">Kembalian: <b id="receiptChange" class="text-emerald-600">Rp 0</b></p>
      </div>
      <div class="mt-3 bg-paper-100 border border-paper-300 rounded-xl p-3 text-sm space-y-1.5" id="receiptSummaryBox">
        <div class="flex justify-between"><span class="text-paper-500">Customer</span><span class="font-semibold truncate ml-2" id="receiptCust">-</span></div>
        <div class="flex justify-between"><span class="text-paper-500">Metode</span><span class="font-semibold" id="receiptMethod">-</span></div>
        <div class="flex justify-between"><span class="text-paper-500">Item</span><span class="font-semibold" id="receiptItems">0</span></div>
      </div>
    </div>
    <div class="pos-modal-footer" style="grid-template-columns:1fr 1fr;row-gap:.5rem">
      <button type="button" class="btn btn-success" onclick="receiptAction('wa')" style="grid-column:span 1">
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#10b981"><path d="M21 11.5a8.4 8.4 0 0 1-12.6 7.3L3 21l1.9-5.4A8.4 8.4 0 1 1 21 11.5Z"/></svg>
        Kirim WA
      </button>
      <button type="button" class="btn btn-secondary" onclick="receiptAction('print')">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Cetak
      </button>
      <button type="button" class="btn btn-primary" onclick="receiptAction('printwa')" style="grid-column:span 2">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Cetak & Kirim WA
      </button>
      <button type="button" class="btn btn-ghost" onclick="receiptAction('close')" style="grid-column:span 2">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
        Close (Transaksi Baru)
      </button>
    </div>
  </div>
</div>

{{-- Modal: Rincian Laundry --}}
<div id="modalLaundry" class="pos-modal-backdrop is-center">
  <div class="pos-modal" style="max-width:420px">
    <div class="pos-modal-header">
      <h3>
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#b45309"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        Rincian Laundry
      </h3>
      <button type="button" onclick="closeModal('modalLaundry')" class="close-btn" aria-label="Tutup">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="pos-modal-body">
      <p class="text-[11px] leading-relaxed text-amber-800/80 mb-3">Pilih jenis lalu tekan <b>Tambah</b>. Tekan Hapus untuk membatalkan baris. <a href="#" onclick="event.preventDefault();openLaundryTypesModal()" class="underline font-semibold">Kelola jenis</a></p>
      <div id="laundryGrid" class="flex flex-col gap-2 mb-3 min-h-[0]">
        <!-- cards ditambah via dropdown -->
      </div>
      <div id="laundryEmpty" class="border border-dashed border-amber-300 rounded-xl bg-white/70 px-3 py-6 text-center">
        <p class="text-sm text-paper-500">Belum ada rincian. Pilih jenis di bawah lalu tambah.</p>
        <p class="text-[11px] text-paper-500 mt-1">Contoh: Baju, Celana, Sepatu, Tas — pcs akan masuk struk</p>
      </div>
      <div class="bg-white border border-amber-200 rounded-xl p-2.5 sm:p-3 space-y-2.5">
        <label class="text-xs font-semibold text-paper-700">Tambah jenis ke rincian</label>
        <div class="flex flex-col sm:flex-row gap-2">
          <select id="laundrySelect" class="flex-1 min-w-0 border border-paper-300 rounded-xl px-3 py-3 text-sm bg-white focus:ring-2 focus:ring-amber-400 outline-none">
            <option value="">— Pilih jenis —</option>
            @foreach($laundryTypes as $t)
            <option value="{{ $t->code }}" data-name="{{ $t->name }}" data-icon="{{ $t->icon ?? '📦' }}">{{ ($t->icon ?? '📦').' '.$t->name }}</option>
            @endforeach
          </select>
          <button type="button" onclick="addLaundryFromSelect()" class="shrink-0 bg-paper-900 active:bg-black text-white px-5 py-3 rounded-xl text-sm font-semibold whitespace-nowrap">+ Tambah</button>
        </div>
        <details class="group">
          <summary class="text-xs text-paper-500 cursor-pointer list-none flex items-center gap-1"><span class="group-open:rotate-90 transition">▶</span> Jenis belum ada? Buat baru</summary>
          <div class="mt-2 flex flex-col sm:flex-row gap-2">
            <input id="newLaundryName" type="text" placeholder="Nama jenis baru, cth: Gorden" class="flex-1 min-w-0 border border-paper-300 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-400 outline-none">
            <input id="newLaundryIcon" type="text" placeholder="Icon 🧹" class="w-full sm:w-24 border border-paper-300 rounded-xl px-3 py-3 text-sm text-center sm:text-left">
            <button type="button" onclick="createLaundryType()" class="shrink-0 bg-white border border-paper-300 active:bg-paper-100 text-paper-800 px-4 py-3 rounded-xl text-sm font-semibold whitespace-nowrap">Buat</button>
          </div>
        </details>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
        <label class="text-xs font-medium text-paper-600">Ket. Lainnya <span class="font-normal text-paper-500">(opsional)</span><input id="laundry_lainnya_desc" type="text" placeholder="Bed cover, gorden, dll" class="mt-1 w-full border border-paper-300 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-400 outline-none"></label>
        <label class="text-xs font-medium text-paper-600">Catatan laundry <span class="font-normal text-paper-500">(masuk struk)</span><input id="laundry_catatan" type="text" placeholder="Noda di kerah, jangan pakai pewangi" class="mt-1 w-full border border-paper-300 rounded-xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-400 outline-none"></label>
      </div>
      <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t">
        <span class="text-[11px] text-paper-500">Total pcs: <b id="laundryTotal" class="text-paper-800">0</b></span>
        <button type="button" onclick="clearLaundry()" class="text-xs text-paper-500 hover:text-paper-700 underline">Kosongkan rincian</button>
      </div>
    </div>
    <div class="pos-modal-footer" style="grid-template-columns:1fr 1fr;row-gap:.5rem">
      <button type="button" class="btn btn-secondary" onclick="closeModal('modalLaundry')">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
        Tutup
      </button>
      <button type="button" class="btn btn-success" onclick="saveLaundryAndClose()">
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#10b981"><path d="M5 13l4 4L19 7"/></svg>
        Simpan & Tutup
      </button>
    </div>
  </div>
</div>
<div id="modalNewCustomer" class="pos-modal-backdrop is-center">
  <div class="pos-modal">
    <div class="pos-modal-header">
      <h3>
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#0f766e"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
        Customer Baru
      </h3>
      <button type="button" onclick="closeModal('modalNewCustomer')" class="close-btn" aria-label="Tutup">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="pos-modal-body">
      <form id="newCustomerForm" onsubmit="return submitNewCustomer(event)">
        <label class="text-xs font-semibold text-paper-600">Nama <span class="text-rose-600">*</span></label>
        <input name="name" required class="w-full mt-1 mb-3 border border-paper-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 outline-none">
        <label class="text-xs font-semibold text-paper-600">No. HP <span class="text-rose-600">*</span></label>
        <input name="phone" required inputmode="tel" class="w-full mt-1 mb-3 border border-paper-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 outline-none">
        <label class="text-xs font-semibold text-paper-600">Alamat <span class="text-paper-500 font-normal">(opsional)</span></label>
        <textarea name="address" rows="2" class="w-full mt-1 border border-paper-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 outline-none"></textarea>
        <p id="newCustomerError" class="text-xs text-rose-600 mt-2 hidden"></p>
      </form>
    </div>
    <div class="pos-modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('modalNewCustomer')">Batal</button>
      <button type="submit" form="newCustomerForm" class="btn btn-primary" id="saveNewCustomerBtn">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
        Simpan
      </button>
    </div>
  </div>
</div>

{{-- Modal: Qty edit --}}
<div id="modalEditQty" class="pos-modal-backdrop is-center">
  <div class="pos-modal" style="max-width:340px">
    <div class="pos-modal-header">
      <h3>
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#0f766e"><path d="M3 6h18M3 12h12M3 18h6"/></svg>
        Ubah Quantity
      </h3>
      <button type="button" onclick="closeModal('modalEditQty')" class="close-btn" aria-label="Tutup">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="pos-modal-body">
      <p class="text-sm font-semibold" id="editQtyName">-</p>
      <p class="text-xs text-paper-500" id="editQtyMeta">-</p>
      <div class="flex items-center justify-center gap-3 mt-4">
        <button type="button" class="pos-laundry-card" style="border:0;background:#f4efe6;border-radius:.6rem;width:3rem;height:3rem;display:grid;place-items:center" onclick="qtyModalStep(-1)">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.2rem;height:1.2rem"><path d="M5 12h14"/></svg>
        </button>
        <input id="editQtyInput" type="number" step="0.1" min="0" class="w-28 border border-paper-300 rounded-xl px-3 py-3 text-center text-xl font-bold focus:ring-2 focus:ring-teal-500 outline-none">
        <button type="button" class="pos-laundry-card" style="border:0;background:#2b2320;color:#fff;border-radius:.6rem;width:3rem;height:3rem;display:grid;place-items:center" onclick="qtyModalStep(1)">
          <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.2rem;height:1.2rem;color:#fff"><path d="M5 12h14M12 5v14"/></svg>
        </button>
      </div>
    </div>
    <div class="pos-modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditQty')">Batal</button>
      <button type="button" class="btn btn-primary" onclick="saveQtyModal()">Simpan</button>
    </div>
  </div>
</div>

{{-- Modal: Manage Laundry Types (popup list) --}}
<div id="modalLaundryTypes" class="pos-modal-backdrop is-center">
  <div class="pos-modal" style="max-width:520px">
    <div class="pos-modal-header">
      <h3>
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#b45309"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        Kelola Jenis Laundry
      </h3>
      <button type="button" onclick="closeModal('modalLaundryTypes')" class="close-btn" aria-label="Tutup">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="pos-modal-body">
      <div class="flex gap-2 mb-3">
        <input id="ltSearch" type="text" placeholder="Cari jenis…" class="flex-1 border border-paper-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-400 outline-none">
        <button type="button" onclick="openCreateLaundryTypeModal()" class="bg-paper-900 text-white px-3 rounded-xl text-sm font-semibold flex items-center gap-1">
          <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
          Tambah
        </button>
      </div>
      <div id="ltList" class="space-y-2 max-h-80 overflow-y-auto"></div>
    </div>
    <div class="pos-modal-footer single">
      <button type="button" class="btn btn-secondary" onclick="closeModal('modalLaundryTypes')">Tutup</button>
    </div>
  </div>
</div>

{{-- Modal: Create new laundry type --}}
<div id="modalCreateLaundryType" class="pos-modal-backdrop is-center">
  <div class="pos-modal" style="max-width:380px">
    <div class="pos-modal-header">
      <h3>
        <svg class="pos-flat-icon" viewBox="0 0 24 24" style="color:#b45309"><path d="M12 5v14M5 12h14"/></svg>
        Jenis Laundry Baru
      </h3>
      <button type="button" onclick="closeModal('modalCreateLaundryType')" class="close-btn" aria-label="Tutup">
        <svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="pos-modal-body">
      <label class="text-xs font-semibold text-paper-600">Nama <span class="text-rose-600">*</span></label>
      <input id="ltName" type="text" class="w-full mt-1 mb-3 border border-paper-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-400 outline-none" placeholder="Cth: Gorden">
      <label class="text-xs font-semibold text-paper-600">Icon (emoji)</label>
      <input id="ltIcon" type="text" maxlength="2" class="w-full mt-1 border border-paper-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-400 outline-none" placeholder="🧹">
    </div>
    <div class="pos-modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateLaundryType')">Batal</button>
      <button type="button" class="btn btn-primary" id="ltSaveBtn" onclick="saveNewLaundryType()">Simpan</button>
    </div>
  </div>
</div>

<script>
let laundryTypes = @json($laundryTypes);
let products = @json($products);
@php
$__editOrderData = null;
if(isset($order)){
  $__editOrderData = [
    'id' => $order->id,
    'order_number' => $order->order_number,
    'customer_id' => $order->customer_id,
    'customer_name' => $order->customer->name ?? 'Walk-in',
    'customer_phone' => $order->customer->phone ?? '',
    'items' => $order->items->map(function($it){ return ['product_id'=>$it->product_id,'name'=>$it->product_name,'sku'=>$it->sku,'price'=>(float)$it->price,'quantity'=>(float)$it->quantity,'unit'=>$it->unit,'discount'=>(float)$it->discount,'type'=>($it->product->type ?? 'product')]; })->values(),
    'laundry_details' => $order->laundry_details,
    'discount' => (float)$order->discount,
    'discount_type' => $order->discount_type ?? 'fixed',
    'tax' => (float)$order->tax,
    'order_status' => $order->order_status,
    'notes' => $order->notes,
  ];
}
@endphp
const editOrder = @json($__editOrderData);
const isEditMode = !!editOrder;
let productMap = {}; products.forEach(p=> productMap[p.id]=p);
let cart = [];
let lastOrderId = null;
let lastOrderData = null;
let selectedLaundry = new Set();
const LAUNDRY_KEY = 'pos_laundry_draft';
let selectedCustomerId = document.getElementById('customerId').value || null;
function updateCustomerRequiredUI(){
  let hint=document.getElementById('customerRequiredHint');
  let has = !!selectedCustomerId;
  if(hint) hint.classList.toggle('hidden', has);
  let box=document.getElementById('selectedCustomer');
  if(box) box.className = has
    ? 'bg-teal-50 border border-teal-300 rounded-lg px-3 py-2.5 text-sm flex justify-between items-center gap-2'
    : 'bg-rose-50 border border-rose-300 rounded-lg px-3 py-2.5 text-sm flex justify-between items-center gap-2';
  let name=document.getElementById('custName');
  if(name) name.className = has ? 'text-teal-900' : 'text-rose-800';
  let clear=box? box.querySelector('button[onclick="clearCustomer()"]') : null;
  if(clear) clear.classList.toggle('hidden', !has);
}
updateCustomerRequiredUI();
if(isEditMode){
  clearLaundryStorage();
  try{
    selectedCustomerId = editOrder.customer_id || null;
    document.getElementById('customerId').value = selectedCustomerId||'';
    document.getElementById('custName').textContent = editOrder.customer_name||'Belum ada customer dipilih';
    document.getElementById('custPhone').textContent = editOrder.customer_phone||'';
    updateCustomerRequiredUI();
    cart = (editOrder.items||[]).map(function(x){return {product_id:x.product_id, name:x.name||x.sku, sku:x.sku, unit:x.unit||'pcs', price:parseFloat(x.price)||0, type:x.type||'product', quantity:parseFloat(x.quantity)||1, discount:parseFloat(x.discount)||0};});
    renderCart();
    // discount/tax/status
    if(editOrder.discount_type) document.getElementById('discountType').value = editOrder.discount_type;
    document.getElementById('discount').value = editOrder.discount||0;
    document.getElementById('tax').value = editOrder.tax||0;
    if(editOrder.order_status) setLaundryStatus(editOrder.order_status==='complete'?'received':editOrder.order_status);
    // laundry_details -> rebuild cards
    var ld = editOrder.laundry_details;
    if(ld && typeof ld==='object'){
      Object.keys(ld).forEach(function(k){
        if(k==='catatan'||k==='lainnya_desc') return;
        var v=parseInt(ld[k],10); if(!v) return;
        var t=laundryTypes.find(function(x){return x.code===k;});
        createLaundryCard(k, (t&&t.name)||k, (t&&t.icon)||'📦', v);
      });
      if(ld.catatan) document.getElementById('laundry_catatan').value=ld.catatan;
      if(ld.lainnya_desc) document.getElementById('laundry_lainnya_desc').value=ld.lainnya_desc;
      updateLaundryTotal();
    }
    document.getElementById('payBtnText').textContent='SIMPAN PERUBAHAN';
    document.getElementById('payBtn').className='w-full bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-bold py-3.5 sm:py-3 rounded-xl text-[15px] shadow flex items-center justify-center gap-2';
  }catch(e){ console.error('preload edit failed', e); }
}

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

// ===== MOBILE CART DRAWER =====
function updateCartUI(){
  let count = cart.reduce((s,c)=> s + (parseFloat(c.quantity)||0), 0);
  const countEl = document.getElementById('cartCount');
  if(countEl) countEl.textContent = count;
  const fab = document.getElementById('cartFab');
  if(fab) fab.classList.toggle('hidden', count<=0);
}
function toggleCartDrawer(){
  const drawer = document.getElementById('cartDrawer');
  const overlay = document.getElementById('cartDrawerOverlay');
  if(!drawer) return;
  const isOpen = drawer.classList.toggle('cart-open');
  document.body.classList.toggle('cart-open', isOpen);
  if(overlay) overlay.classList.toggle('show', isOpen);
  if(!isOpen){
    // close any open modals on top of the drawer
    document.querySelectorAll('.pos-modal-backdrop.is-open').forEach(m=> m.classList.remove('is-open'));
    document.body.classList.remove('pos-modal-open');
  }
}
const drawerOverlayEl = document.getElementById('cartDrawerOverlay');
if(drawerOverlayEl) drawerOverlayEl.addEventListener('click', toggleCartDrawer);
function renderCart(){
  let wrap = document.getElementById('cartItems');
  if(cart.length===0){
    wrap.innerHTML='<div class="pos-empty"><svg class="empty-icon pos-flat-icon" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg><p class="text-paper-700">Keranjang masih kosong</p><p class="text-paper-500 mt-1">Ketuk produk untuk menambah</p></div>';
    calc(); updateCartUI(); syncOptionalVisibility(); return;
  }
  let html='';
  cart.forEach((c,i)=>{
    html+= `<div class="pos-cart-item">
      <div class="ci-top">
        <div class="min-w-0">
          <div class="ci-name">${c.name}</div>
          <div class="ci-meta">${money(c.price)} / ${c.unit}</div>
        </div>
        <div class="ci-subtotal">${money(c.quantity*c.price - c.discount)}</div>
      </div>
      <div class="ci-bottom">
        <div class="ci-qty">
          <button onclick="changeQty(${i},-1)" aria-label="Kurangi jumlah ${c.name}">
            <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.25rem;height:1.25rem"><path d="M5 12h14"/></svg>
          </button>
          <input type="number" value="${c.quantity}" step="${c.type==='service'?0.1:1}" min="0.001"
                 onchange="updQty(${i},this.value)" onclick="openQtyModal(${i})" readonly
                 aria-label="Jumlah ${c.name}">
          <button onclick="changeQty(${i},1)" aria-label="Tambah jumlah ${c.name}">
            <svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:1.25rem;height:1.25rem"><path d="M5 12h14M12 5v14"/></svg>
          </button>
        </div>
        <button onclick="removeItem(${i})" class="ci-remove" aria-label="Hapus ${c.name} dari keranjang">Hapus</button>
      </div>
    </div>`;
  });
  wrap.innerHTML=html;
  calc();
  updateCartUI();
  syncOptionalVisibility();
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
  // Kembalian hanya tampil kalau memang ada kembalian.
  let row = document.getElementById('changeRow');
  if(row) row.classList.toggle('hidden', change <= 0);
  // Diskon yang aktif harus terlihat di baris total, kalau tidak kasir
  // mengira diskon tidak berlaku karena tidak ada di mana pun.
  let discEl = document.getElementById('discountLine');
  if(discEl){
    let hasDisc = disc > 0;
    discEl.classList.toggle('hidden', !hasDisc);
    if(hasDisc) document.getElementById('discountLineValue').textContent = '-'+money(disc);
  }
  let taxEl = document.getElementById('taxLine');
  if(taxEl){
    let hasTax = tax > 0;
    taxEl.classList.toggle('hidden', !hasTax);
    if(hasTax) document.getElementById('taxLineValue').textContent = money(tax);
  }
  return {sub,disc,tax,total,paid,change};
}
let optionalFieldsOpen = false;
function toggleOptionalFields(){
  optionalFieldsOpen = !optionalFieldsOpen;
  let box = document.getElementById('optionalFields');
  let icon = document.getElementById('optionalToggleIcon');
  let text = document.getElementById('optionalToggleText');
  if(box) box.classList.toggle('hidden', !optionalFieldsOpen);
  if(icon) icon.style.transform = optionalFieldsOpen ? 'rotate(90deg)' : '';
  if(text) text.textContent = optionalFieldsOpen ? 'Sembunyikan diskon dan pajak' : 'Tambah diskon atau pajak';
}
// Begitu ada isinya, baris diskon dan pajak ikut tampil di area total,
// supaya kasir bisa langsung cek hasilnya tanpa menggulir ke panel atas.
// Tombol buka panel disembunyikan selama tidak ada yang perlu diisi.
function syncOptionalVisibility(){
  let disc = parseFloat(document.getElementById('discount').value)||0;
  let tax  = parseFloat(document.getElementById('tax').value)||0;
  let any  = disc > 0 || tax > 0;
  let toggleRow = document.getElementById('optionalToggleRow');
  if(!toggleRow) return;
  // Botolnya hanya relevan kalau sudah ada barang. Kalau cart kosong,
  // tombol diskon hanya menambah sesuatu yang tidak ada gunanya.
  if(any || cart.length === 0) toggleRow.classList.add('hidden');
  else toggleRow.classList.remove('hidden');
}
['discount','discountType','tax','paidAmount'].forEach(id=> document.getElementById(id).addEventListener('input', function(){
  calc();
  syncOptionalVisibility();
}));
['laundry_lainnya_desc','laundry_catatan'].forEach(id=>{
  let el=document.getElementById(id);
  if(el) el.addEventListener('change', saveLaundryDraft);
});
function filterCat(cat){
  document.querySelectorAll('.pos-cat-pill').forEach(b=>{
    let active = b.dataset.cat===cat || (cat==='' && b.dataset.cat==='');
    b.classList.toggle('is-active', active);
  });
  document.querySelectorAll('.productCard').forEach(el=>{ el.style.display = (!cat || el.dataset.cat===cat) ? '' : 'none'; });
}
document.querySelectorAll('.pos-cat-pill').forEach(b=>{
  let m = b.getAttribute('onclick')?.match(/'([^']*)'/);
  b.dataset.cat = m?m[1] : '';
});
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
      if(!data.length){ box.innerHTML='<div class="p-3 text-sm text-paper-500">Tidak ditemukan</div>'; box.classList.remove('hidden'); return; }
      box.innerHTML = data.map(c=> `<button type="button" onclick="selectCustomer(${c.id},'${c.name.replace(/'/g, "&apos;")}', '${c.phone}')" class="w-full text-left px-3 py-2.5 hover:bg-paper-100 text-sm border-b last:border-0 flex items-center gap-2"><span class="w-7 h-7 rounded-full bg-teal-100 text-teal-700 grid place-items-center font-semibold text-xs shrink-0">${(c.name||'?').charAt(0).toUpperCase()}</span><span class="min-w-0 flex-1"><b class="block truncate">${c.name}</b><span class="text-paper-500 text-xs">${c.phone} • ${c.code}</span></span></button>`).join('');
      box.classList.remove('hidden');
    });
  },250);
});
function selectCustomer(id,name,phone){
  selectedCustomerId=id; document.getElementById('customerId').value=id;
  document.getElementById('custName').textContent=name; document.getElementById('custPhone').textContent=phone;
  document.getElementById('customerResults').classList.add('hidden');
  document.getElementById('customerSearch').value='';
  updateCustomerRequiredUI();
}
function clearCustomer(){
  selectedCustomerId=null;
  document.getElementById('customerId').value='';
  document.getElementById('custName').textContent='Belum ada customer dipilih';
  document.getElementById('custPhone').textContent='';
  updateCustomerRequiredUI();
}

// --- Rincian Laundry: open via modal popup, persist via localStorage ---
// selectedLaundry & LAUNDRY_KEY declared at top init

function saveLaundryDraft(){
  let d = {};
  document.querySelectorAll('.laundryInput').forEach(el=>{
    let v=parseInt(el.value||'0',10);
    if(v>0) d[el.dataset.code]=v;
  });
  let desc = document.getElementById('laundry_lainnya_desc')?.value || '';
  let catatan = document.getElementById('laundry_catatan')?.value || '';
  let payload = { items: d };
  if (desc) payload.lainnya_desc = desc;
  if (catatan) payload.catatan = catatan;
  try { localStorage.setItem(LAUNDRY_KEY, JSON.stringify(payload)); } catch(e){}
}

function saveLaundryAndClose(){
  saveLaundryDraft();
  closeModal('modalLaundry');
}

function restoreLaundryDraft(){
  if(isEditMode && editOrder && editOrder.laundry_details){
    let ld = editOrder.laundry_details;
    selectedLaundry.clear();
    let grid = document.getElementById('laundryGrid');
    if (grid) grid.innerHTML = '';
    Object.keys(ld).forEach(code=>{
      if(code==='catatan'||code==='lainnya_desc') return;
      let v = parseInt(ld[code],10);
      if(v>0){
        let t = laundryTypes.find(x=> x.code===code);
        createLaundryCard(code, (t && t.name) || code, (t && t.icon) || '📦', v);
      }
    });
    let descEl = document.getElementById('laundry_lainnya_desc');
    if (descEl) descEl.value = ld.lainnya_desc || '';
    let catEl = document.getElementById('laundry_catatan');
    if (catEl) catEl.value = ld.catatan || '';
    refreshLaundrySelect();
    updateLaundryTotal();
    return;
  }
  // new order: restore from localStorage draft (ignore stale draft in edit mode)
  if(isEditMode) return;
  let raw = localStorage.getItem(LAUNDRY_KEY);
  if (!raw) return;
  let payload;
  try { payload = JSON.parse(raw); } catch(e){ return; }
  let items = payload.items || {};
  selectedLaundry.clear();
  let grid = document.getElementById('laundryGrid');
  if (grid) grid.innerHTML = '';
  Object.keys(items).forEach(code=>{
    let it = items[code];
    if (typeof it === 'number' && it > 0) {
      let t = laundryTypes.find(x=> x.code===code);
      createLaundryCard(code, (t && t.name) || code, (t && t.icon) || '📦', it);
    }
  });
  let descEl = document.getElementById('laundry_lainnya_desc');
  if (descEl && payload.lainnya_desc) descEl.value = payload.lainnya_desc;
  let catEl = document.getElementById('laundry_catatan');
  if (catEl && payload.catatan) catEl.value = payload.catatan;
  refreshLaundrySelect();
  updateLaundryTotal();
}
// clear localStorage draft so rincian laundry jangan 'nge-cache' data order lama ketika edit
function clearLaundryStorage(){
  try { localStorage.removeItem(LAUNDRY_KEY); } catch(e){}
  selectedLaundry.clear();
  let grid = document.getElementById('laundryGrid');
  if (grid) grid.innerHTML = '';
}

function openLaundryModal(){
  // Hanya restore kalau modal masih kosong (pertama kali buka atau setelah clear)
  let grid = document.getElementById('laundryGrid');
  if (grid && grid.children.length === 0) {
    restoreLaundryDraft();
  }
  openModal('modalLaundry');
}

function clearLaundry(){
  document.getElementById('laundryGrid').innerHTML='';
  selectedLaundry.clear();
  ['laundry_lainnya_desc','laundry_catatan'].forEach(id=>{
    let el=document.getElementById(id); if(el) el.value='';
  });
  refreshLaundrySelect();
  updateLaundryTotal();
  try { localStorage.removeItem(LAUNDRY_KEY); } catch(e){}
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

function createLaundryCard(code, name, icon, initialValue){
  if(selectedLaundry.has(code)){
    let el=document.getElementById('laundry_'+code);
    if(el) el.focus();
    return;
  }
  selectedLaundry.add(code);
  let grid=document.getElementById('laundryGrid');
  let wrap=document.createElement('div');
  wrap.id='wrap_'+code;
  wrap.className='pos-laundry-card';
  var iconDisp = icon || '\u{1F4CC}';
  wrap.innerHTML =
    '<div class="ll-icon">'+iconDisp+'</div>'+
    '<div class="ll-name">'+name+'</div>'+
    '<button type="button" onclick="stepLaundry(\''+code+'\',-1)" class="ll-step minus" aria-label="Kurangi">'+
      '<svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.95rem;height:.95rem"><path d="M5 12h14"/></svg>'+
    '</button> '+
    '<input id="laundry_'+code+'" data-code="'+code+'" type="number" min="0" inputmode="numeric" placeholder="0" class="ll-input laundryInput" value="'+(initialValue||'')+'"> '+
    '<button type="button" onclick="stepLaundry(\''+code+'\',1)" class="ll-step plus" aria-label="Tambah">'+
      '<svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.95rem;height:.95rem;color:#fff"><path d="M5 12h14M12 5v14"/></svg>'+
    '</button> '+
    '<button type="button" onclick="removeLaundryRow(\''+code+'\')" class="ll-remove" aria-label="Hapus baris" title="Hapus baris">'+
      '<svg class="pos-flat-icon" viewBox="0 0 24 24" style="width:.95rem;height:.95rem"><path d="M18 6 6 18M6 6l12 12"/></svg>'+
    '</button>';
  grid.appendChild(wrap);
  wrap.querySelector('.laundryInput').addEventListener('input', updateLaundryTotal);
  wrap.querySelector('.laundryInput').addEventListener('change', saveLaundryDraft);
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
  saveLaundryDraft();
}
function removeLaundryRow(code){
  let el=document.getElementById('wrap_'+code);
  if(el) el.remove();
  selectedLaundry.delete(code);
  refreshLaundrySelect();
  updateLaundryTotal();
  saveLaundryDraft();
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
  if(v==='received'){
    a.classList.add('is-active','received'); a.classList.remove('ready');
    b.classList.remove('is-active','received','ready');
  } else {
    b.classList.add('is-active','ready'); b.classList.remove('received');
    a.classList.remove('is-active','received','ready');
  }
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

// ============ MODAL HELPERS ============
function openModal(id){ document.getElementById(id).classList.add('is-open'); document.body.classList.add('pos-modal-open'); }
function closeModal(id){ document.getElementById(id).classList.remove('is-open'); document.body.classList.remove('pos-modal-open'); }
document.addEventListener('click', e=>{
  if(e.target.classList && e.target.classList.contains('pos-modal-backdrop')){
    e.target.classList.remove('is-open');
    document.body.classList.remove('pos-modal-open');
  }
});
document.addEventListener('keydown', e=>{
  if(e.key==='Escape'){
    document.querySelectorAll('.pos-modal-backdrop.is-open').forEach(m=> m.classList.remove('is-open'));
    document.body.classList.remove('pos-modal-open');
  }
});

async function submitEditOrder(){
  if(cart.length===0){ alert('Keranjang kosong'); return; }
  let btn=document.getElementById('payBtn'); if(btn.disabled) return;
  btn.disabled=true; let prev=btn.innerHTML; btn.innerHTML='<span>Menyimpan...</span>';
  try{
    let payload={
      customer_id: selectedCustomerId||null,
      items: cart.map(function(c){return {product_id:c.product_id, quantity:c.quantity, price:c.price, discount:c.discount};}),
      discount: (function(){ let v=parseFloat(document.getElementById('discount').value)||0; return document.getElementById('discountType').value==='percent'?v:v; })(),
      discount_type: document.getElementById('discountType').value,
      tax: parseFloat(document.getElementById('tax').value)||0,
      order_status: document.getElementById('orderStatus').value||'received',
      laundry_details: getLaundryDetails(),
      notes: (editOrder&&editOrder.notes)||null,
      _token: '{{ csrf_token() }}'
    };
    let res=await fetch('{{ isset($order) ? route('pos.update',$order) : route('pos.store') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: JSON.stringify(payload)});
    let data=await res.json();
    if(!res.ok){ btn.disabled=false; btn.innerHTML=prev; alert(data.message||JSON.stringify(data.errors||data)); return; }
    window.location.href='/orders/'+data.id;
  }catch(e){ btn.disabled=false; btn.innerHTML=prev; alert(e.message); }
}

// ============ CHECKOUT MODAL ============
function openCheckoutModal(){
  if(cart.length===0){ alert('Keranjang kosong'); return; }
  if(!selectedCustomerId){ updateCustomerRequiredUI(); document.getElementById('customerSearch').focus(); alert('Pilih customer dulu — wajib isi customer sebelum bayar'); return; }
  let {sub,disc,tax,total}=calc();
  let custName = document.getElementById('custName').textContent;
  let itemCount = cart.reduce((s,c)=> s + (parseFloat(c.quantity)||0), 0);
  let html = `
    <div class="pos-section" style="background:#faf7f1;padding:.75rem">
      <div class="pos-summary-row"><span class="label">Customer</span><span class="value">${custName}</span></div>
      <div class="pos-summary-row"><span class="label">Item</span><span class="value">${itemCount} item</span></div>
      <div class="pos-summary-row"><span class="label">Subtotal</span><span class="value">${money(sub)}</span></div>
      <div class="pos-summary-row"><span class="label">Diskon</span><span class="value text-rose-600">-${money(disc)}</span></div>
      <div class="pos-summary-row"><span class="label">Pajak</span><span class="value">${money(tax)}</span></div>
      <div class="pos-summary-row total"><span>TOTAL</span><span class="value" id="modalTotalInline">${money(total)}</span></div>
    </div>
  `;
  document.getElementById('checkoutSummary').innerHTML = html;
  document.getElementById('modalTotalText').textContent = money(total);
  let paid = parseFloat(document.getElementById('paidAmount').value)||0;
  document.getElementById('modalPaidAmount').value = paid;
  document.getElementById('modalPaidText').textContent = money(paid);
  let change = paid>total ? paid-total : 0;
  document.getElementById('modalChangeText').textContent = money(change);
  document.getElementById('modalChangeRow').style.display = change>0 ? 'flex' : 'none';
  // Sync method
  let cur = document.getElementById('paymentMethod').value;
  document.querySelectorAll('#checkoutPayMethods button').forEach(b=>{
    b.classList.toggle('is-active', b.dataset.method===cur);
  });
  // Set quick cash defaults based on total
  let qc=document.getElementById('quickCashButtons');
  let t = total;
  qc.querySelectorAll('button[data-amt]').forEach((b,i)=>{
    let mult = [2,5,10][i] || 1;
    b.textContent = 'Rp ' + (t*mult).toLocaleString('id-ID',{maximumFractionDigits:0});
    b.dataset.amt = Math.ceil(t*mult/1000)*1000;
  });
  openModal('modalCheckout');
  setTimeout(()=>{ document.getElementById('modalPaidAmount').focus(); document.getElementById('modalPaidAmount').select(); }, 200);
}
function selectPayMethod(btn, method){
  document.querySelectorAll('#checkoutPayMethods button').forEach(b=>b.classList.remove('is-active'));
  btn.classList.add('is-active');
  document.getElementById('paymentMethod').value = method;
}
document.getElementById('modalPaidAmount')?.addEventListener('input', function(){
  let total = parseFloat((document.getElementById('modalTotalText').textContent||'0').replace(/[^\d]/g,'')) || 0;
  let paid = parseFloat(this.value)||0;
  let change = paid>total ? paid-total : 0;
  document.getElementById('modalPaidText').textContent = money(paid);
  document.getElementById('modalChangeText').textContent = money(change);
  document.getElementById('modalChangeRow').style.display = change>0 ? 'flex' : 'none';
  document.getElementById('paidAmount').value = paid;
});
function quickCash(mode){
  let total = parseFloat((document.getElementById('modalTotalText').textContent||'0').replace(/[^\d]/g,'')) || 0;
  let paid = 0;
  if(mode==='exact'){ paid = total; }
  else {
    paid = parseFloat(mode)||0;
  }
  document.getElementById('modalPaidAmount').value = paid;
  document.getElementById('modalPaidAmount').dispatchEvent(new Event('input'));
}

async function confirmCheckout(){
  let btn = document.getElementById('modalConfirmPayBtn');
  if(btn.disabled) return;
  let paid = parseFloat(document.getElementById('modalPaidAmount').value)||0;
  let total = parseFloat((document.getElementById('modalTotalText').textContent||'0').replace(/[^\d]/g,'')) || 0;
  if(paid < total){
    if(!confirm('Bayar kurang dari total. Lanjutkan?')) return;
  }
  // sync back to hidden fields
  document.getElementById('paidAmount').value = paid;
  btn.disabled=true; btn.innerHTML='<span>Memproses...</span>';
  try{
    let {total:_, paid:__} = calc();
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
    let res= await fetch('{{ route('pos.store') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: JSON.stringify(payload)});
    let data= await res.json();
    if(!res.ok){
      btn.disabled=false;
      btn.innerHTML='<svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="m20 6-11 11-5-5"/></svg> Bayar Sekarang';
      alert(data.message || JSON.stringify(data.errors || data));
      return;
    }
    lastOrderId = data.id;
    lastOrderData = data;
    closeModal('modalCheckout');
    // Laundry draft sudah terpakai — bersihkan agar transaksi baru mulai fresh
    try { localStorage.removeItem(LAUNDRY_KEY); selectedLaundry.clear(); document.getElementById('laundryGrid').innerHTML=''; } catch(e){}
    // Reset mobile cart drawer & FAB setelah transaksi sukses (cart kosong)
    try {
      let _d=document.getElementById('cartDrawer'); if(_d) _d.classList.remove('cart-open');
      let _o=document.getElementById('cartDrawerOverlay'); if(_o) _o.classList.remove('show');
      document.body.classList.remove('cart-open');
      if(document.getElementById('cartFab')) document.getElementById('cartFab').classList.add('hidden');
      if(document.getElementById('cartCount')) document.getElementById('cartCount').textContent='0';
    } catch(e){}
    // Build receipt modal
    let change = paid>total ? paid-total : 0;
    document.getElementById('receiptOrderNumber').textContent = data.order_number || ('Order #'+data.id);
    document.getElementById('receiptTotal').textContent = money(total);
    document.getElementById('receiptChange').textContent = money(change);
    let custName = document.getElementById('custName').textContent;
    document.getElementById('receiptCust').textContent = custName;
    let methodLabel = {cash:'Cash',transfer:'Transfer',qris:'QRIS',debit:'Debit',e_wallet:'E-Wallet'}[document.getElementById('paymentMethod').value] || '-';
    document.getElementById('receiptMethod').textContent = methodLabel;
    document.getElementById('receiptItems').textContent = cart.length + ' produk';
    openModal('modalReceipt');
    btn.disabled=false;
    btn.innerHTML='<svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="m20 6-11 11-5-5"/></svg> Bayar Sekarang';
  }catch(e){
    btn.disabled=false;
    btn.innerHTML='<svg class="pos-flat-icon" viewBox="0 0 24 24"><path d="m20 6-11 11-5-5"/></svg> Bayar Sekarang';
    alert(e.message);
  }
}

// Receipt actions
async function receiptAction(act){
  if(!lastOrderId){ return; }
  if(act==='close'){
    closeReceiptModal();
    location.reload();
    return;
  }
  if(act==='print'){
    window.open('/orders/'+lastOrderId+'/print','_blank','width=400,height=600');
    return;
  }
  if(act==='wa'){
    // Redirect langsung ke WhatsApp tanpa fetch/Swal popup
    window.open('/orders/'+lastOrderId+'/whatsapp','_blank','width=400,height=600');
    return;
  }
  if(act==='printwa'){
    window.open('/orders/'+lastOrderId+'/print','_blank','width=400,height=600');
    window.open('/orders/'+lastOrderId+'/whatsapp','_blank','width=400,height=600');
    return;
  }
}
function closeReceiptModal(){ closeModal('modalReceipt'); }

// ============ NEW CUSTOMER MODAL ============
function openNewCustomerModal(){
  document.getElementById('newCustomerForm').reset();
  document.getElementById('newCustomerError').classList.add('hidden');
  openModal('modalNewCustomer');
  setTimeout(()=>{ document.querySelector('#newCustomerForm [name=name]').focus(); }, 150);
}
async function submitNewCustomer(e){
  if(e) e.preventDefault();
  let form = document.getElementById('newCustomerForm');
  let fd = new FormData(form);
  let btn = document.getElementById('saveNewCustomerBtn');
  if(btn.disabled) return false;
  btn.disabled = true;
  try{
    let res = await fetch('{{ route('api.customers.store') }}',{method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: fd});
    let data = await res.json();
    if(!res.ok){
      let msg = data.message || Object.values(data.errors||{}).flat().join(', ') || 'Gagal membuat customer';
      document.getElementById('newCustomerError').textContent = msg;
      document.getElementById('newCustomerError').classList.remove('hidden');
      btn.disabled = false;
      return false;
    }
    selectCustomer(data.id, data.name, data.phone||'');
    closeModal('modalNewCustomer');
    btn.disabled = false;
    return false;
  }catch(err){
    document.getElementById('newCustomerError').textContent = err.message;
    document.getElementById('newCustomerError').classList.remove('hidden');
    btn.disabled = false;
    return false;
  }
}

// ============ QTY EDIT MODAL ============
let qtyModalIndex = null;
function openQtyModal(i){
  qtyModalIndex = i;
  let c = cart[i];
  document.getElementById('editQtyName').textContent = c.name;
  document.getElementById('editQtyMeta').textContent = c.sku + ' • ' + money(c.price) + '/' + c.unit;
  document.getElementById('editQtyInput').value = c.quantity;
  document.getElementById('editQtyInput').step = c.type==='service' ? 0.1 : 1;
  openModal('modalEditQty');
  setTimeout(()=>{ document.getElementById('editQtyInput').focus(); document.getElementById('editQtyInput').select(); }, 150);
}
function qtyModalStep(d){
  let c = cart[qtyModalIndex];
  if(!c) return;
  let step = c.type==='service' ? 0.5 : 1;
  let v = parseFloat((parseFloat(document.getElementById('editQtyInput').value||0) + d*step).toFixed(3));
  if(v<=0){ return; }
  if(c.type==='product' && !Number.isInteger(v)) v = Math.round(v);
  document.getElementById('editQtyInput').value = v;
}
function saveQtyModal(){
  let v = parseFloat(document.getElementById('editQtyInput').value);
  if(!v || v<=0){ alert('Quantity harus > 0'); return; }
  updQty(qtyModalIndex, v);
  closeModal('modalEditQty');
}

// ============ LAUNDRY TYPES MODAL ============
function openLaundryTypesModal(){
  document.getElementById('ltSearch').value='';
  renderLaundryTypeList('');
  openModal('modalLaundryTypes');
  setTimeout(()=>{ document.getElementById('ltSearch').focus(); }, 150);
}
function renderLaundryTypeList(q){
  q = (q||'').toLowerCase();
  let list = document.getElementById('ltList');
  let filtered = laundryTypes.filter(t=> !q || (t.name||'').toLowerCase().includes(q));
  if(!filtered.length){ list.innerHTML='<p class="text-sm text-paper-500 text-center py-6">Belum ada jenis laundry</p>'; return; }
  list.innerHTML = filtered.map(t=>`
    <div class="flex items-center gap-3 p-2.5 border border-paper-300 rounded-xl bg-white">
      <span class="w-9 h-9 grid place-items-center rounded-lg bg-amber-100 text-base shrink-0">${t.icon||'📦'}</span>
      <div class="flex-1 min-w-0">
        <div class="font-semibold text-sm truncate">${t.name}</div>
        <div class="text-[11px] text-paper-500 font-mono">${t.code}</div>
      </div>
      <button type="button" onclick="addLaundryTypeToOrder('${t.code}')" class="px-3 py-1.5 bg-paper-900 text-white rounded-lg text-xs font-semibold">+ Pakai</button>
    </div>
  `).join('');
}
document.getElementById('ltSearch')?.addEventListener('input', function(){ renderLaundryTypeList(this.value); });
function addLaundryTypeToOrder(code){
  let t = laundryTypes.find(x=> x.code===code);
  if(!t) return;
  createLaundryCard(t.code, t.name, t.icon||'📦');
  closeModal('modalLaundryTypes');
}
function openCreateLaundryTypeModal(){
  document.getElementById('ltName').value='';
  document.getElementById('ltIcon').value='';
  openModal('modalCreateLaundryType');
  setTimeout(()=>{ document.getElementById('ltName').focus(); }, 150);
}
async function saveNewLaundryType(){
  let name = (document.getElementById('ltName').value||'').trim();
  let icon = (document.getElementById('ltIcon').value||'').trim() || '📦';
  if(!name){ alert('Isi nama jenis'); return; }
  let btn = document.getElementById('ltSaveBtn');
  if(!btn){ alert('Tombol simpan tidak ditemukan'); return; }
  if(btn.disabled) return false;
  btn.disabled = true;
  try{
    let res = await fetch('{{ route('api.laundry-types.store') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:JSON.stringify({name,icon})});
    let data = await res.json();
    if(!res.ok){ alert(data.message||'Gagal'); btn.disabled=false; return; }
    laundryTypes.push(data);
    laundryTypes.sort((a,b)=> (a.sort_order||0)-(b.sort_order||0));
    refreshLaundrySelect();
    renderLaundryTypeList(document.getElementById('ltSearch').value);
    closeModal('modalCreateLaundryType');
    btn.disabled = false;
  } catch(e){ alert(e.message); btn.disabled=false; }
}

document.addEventListener('click', e=>{
  let box=document.getElementById('customerResults');
  if(!e.target.closest('#customerSearch') && !e.target.closest('#customerResults')) box.classList.add('hidden');
});

// Expose for global
window.checkout = openCheckoutModal;
</script>
@endsection
