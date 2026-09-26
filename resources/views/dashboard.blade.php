@extends('layouts.app')
@section('title','Dashboard')

@section('content')
{{--
  Satu fokus layar ini: antrian yang perlu dikerjakan sekarang.
  Omzet ada tapi sekunder, karena omzet dipantau akhir hari,
  antrian itu pekerjaan yang sedang berjalan.

  Layout: dua kolom tidak simetris (2fr / 1fr). Kiri kerja, kanan pantau.
  Bukan grid 4 stat card seragam karena keempat angka itu tidak setara
  prioritas: antrian adalah tugas, kas dan piutang adalah catatan.
--}}

<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 mb-5">
  <div>
    <h1 class="text-xl sm:text-2xl font-semibold text-paper-800">Dashboard</h1>
    <p class="text-sm text-paper-500 mt-0.5">
      {{ current_branch()? current_branch()->name : 'Semua Cabang' }}
    </p>
  </div>
  <p class="text-sm text-paper-500">{{ now()->format('d M Y') }}</p>
</div>

{{-- Omzet hari ini. Satu blok, bukan salah satu dari empat kartu,
     karena ini satu-satunya angka yang ditanyakan pertama. --}}
<div class="bg-white border border-paper-300 rounded-lg px-5 py-4 mb-5">
  <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2">
    <div>
      <p class="text-xs font-semibold text-paper-500 uppercase tracking-wider">Omzet hari ini</p>
      <p class="text-3xl sm:text-4xl font-bold text-paper-800 mt-1 tabular-nums">{{ money($stats['today_sales']) }}</p>
    </div>
    <p class="text-sm text-paper-500">
      dari <b class="text-paper-700 font-semibold tabular-nums">{{ $stats['today_orders'] }}</b> transaksi
    </p>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 lg:gap-5">

  {{-- KOLOM KIRI: pekerjaan yang harus dikerjakan. --}}
  <div class="lg:col-span-2 space-y-4 lg:space-y-5">

    {{-- Antrian: laundry diterima, belum selesai. --}}
    <section class="bg-white border border-paper-300 rounded-lg overflow-hidden">
      <header class="px-4 sm:px-5 py-3.5 border-b border-paper-300 flex items-center justify-between gap-3">
        <div class="flex items-baseline gap-2.5 min-w-0">
          <h2 class="font-semibold text-paper-800">Sedang dicuci</h2>
          <span class="text-sm text-paper-500 tabular-nums">{{ $stats['pending_laundry'] }}</span>
        </div>
        <a href="{{ route('queue.index') }}" class="text-sm text-teal-700 hover:underline shrink-0">Buka antrian</a>
      </header>

      @forelse($queueList as $o)
        <a href="{{ route('orders.show',$o) }}"
           class="flex items-center gap-3 px-4 sm:px-5 py-3 border-b border-paper-200 last:border-0 hover:bg-paper-100 focus-visible:bg-paper-100">
          {{-- Nomor tag. Motif identitas: setiap order punya tag fisik,
               jadi nomornya tampil monospace, bukan angka biasa. --}}
          <span class="font-mono text-xs font-semibold text-paper-600 bg-paper-200 px-2 py-1 rounded shrink-0 tabular-nums">
            {{ $o->order_number }}
          </span>
          <span class="flex-1 min-w-0">
            <span class="block text-paper-800 truncate">{{ $o->customer->name ?? 'Walk-in' }}</span>
            <span class="block text-xs text-paper-500">{{ $o->order_date->format('d/m H:i') }}</span>
          </span>
          <span class="text-paper-700 font-semibold shrink-0 tabular-nums">{{ money($o->total) }}</span>
        </a>
      @empty
        {{-- Empty state: kenapa kosong dan apa selanjutnya. --}}
        <div class="px-4 sm:px-5 py-10 text-center">
          <p class="text-paper-700">Tidak ada laundry yang sedang dicuci.</p>
          <p class="text-sm text-paper-500 mt-1">Order masuk ke sini setelah kasir menerimanya.</p>
          <a href="{{ route('pos.index') }}" class="inline-block mt-4 text-sm text-teal-700 hover:underline">Buka POS</a>
        </div>
      @endforelse
    </section>

    {{-- Siap diambil: aksinya berbeda (hubungi customer), jadi dipisah. --}}
    @if($readyList->isNotEmpty())
    <section class="bg-white border border-amber-300 rounded-lg overflow-hidden">
      <header class="px-4 sm:px-5 py-3.5 border-b border-amber-200 flex items-center justify-between gap-3">
        <div class="flex items-baseline gap-2.5 min-w-0">
          <h2 class="font-semibold text-amber-900">Siap diambil</h2>
          <span class="text-sm text-amber-700 tabular-nums">{{ $stats['ready_laundry'] }}</span>
        </div>
        <span class="text-xs text-amber-700 shrink-0">Hubungi customer</span>
      </header>
      @foreach($readyList as $o)
        <a href="{{ route('orders.show',$o) }}"
           class="flex items-center gap-3 px-4 sm:px-5 py-3 border-b border-amber-100 last:border-0 hover:bg-amber-50 focus-visible:bg-amber-50">
          <span class="font-mono text-xs font-semibold text-amber-800 bg-amber-100 px-2 py-1 rounded shrink-0 tabular-nums">
            {{ $o->order_number }}
          </span>
          <span class="flex-1 min-w-0">
            <span class="block text-paper-800 truncate">{{ $o->customer->name ?? 'Walk-in' }}</span>
            <span class="block text-xs text-paper-500">{{ $o->customer->phone ?? 'Tanpa nomor HP' }}</span>
          </span>
        </a>
      @endforeach
    </section>
    @endif

    {{-- Transaksi terbaru: catatan, bukan tugas. --}}
    <section class="bg-white border border-paper-300 rounded-lg overflow-hidden">
      <header class="px-4 sm:px-5 py-3.5 border-b border-paper-300 flex items-center justify-between gap-3">
        <h2 class="font-semibold text-paper-800">Transaksi terbaru</h2>
        <a href="{{ route('orders.index') }}" class="text-sm text-teal-700 hover:underline shrink-0">Semua order</a>
      </header>

      {{-- Desktop tabel, mobile kartu. Dua bentuk karena readability beda,
           bukan karena dekorasi. --}}
      <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-paper-500 text-xs">
            <tr>
              <th class="text-left font-semibold py-2.5 px-5">No. order</th>
              <th class="text-left font-semibold">Customer</th>
              <th class="text-right font-semibold">Total</th>
              <th class="text-center font-semibold pr-5">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recent as $o)
            <tr class="border-t border-paper-200 hover:bg-paper-100">
              <td class="py-2.5 px-5 font-mono text-xs text-paper-600">{{ $o->order_number }}</td>
              <td class="text-paper-800">{{ $o->customer->name ?? 'Walk-in' }}</td>
              <td class="text-right font-medium text-paper-800 tabular-nums">{{ money($o->total) }}</td>
              <td class="text-center pr-5">
                @php $__st = $o->order_status; @endphp
                <span class="inline-block px-2 py-0.5 rounded text-xs border
                  {{ $__st==='ready' ? 'bg-amber-50 border-amber-300 text-amber-800'
                   : ($__st==='complete' ? 'bg-emerald-50 border-emerald-300 text-emerald-800'
                   : 'bg-paper-200 border-paper-300 text-paper-700') }}">
                  {{ $__st==='ready' ? 'Siap' : ($__st==='complete' ? 'Selesai' : 'Dicuci') }}
                </span>
              </td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-8 text-center text-paper-500">Belum ada transaksi</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="sm:hidden divide-y divide-paper-200">
        @forelse($recent as $o)
        <a href="{{ route('orders.show',$o) }}" class="flex justify-between gap-3 px-4 py-3 hover:bg-paper-100">
          <span class="min-w-0">
            <span class="block font-mono text-xs font-semibold text-paper-600">{{ $o->order_number }}</span>
            <span class="block text-sm text-paper-800 truncate">{{ $o->customer->name ?? 'Walk-in' }}</span>
          </span>
          <span class="text-right shrink-0">
            <span class="block font-semibold text-sm text-paper-800 tabular-nums">{{ money($o->total) }}</span>
            <span class="block text-xs text-paper-500">{{ $o->order_date? $o->order_date->format('d/m') : '' }}</span>
          </span>
        </a>
        @empty
          <p class="py-8 text-center text-sm text-paper-500">Belum ada transaksi</p>
        @endforelse
      </div>
    </section>
  </div>

  {{-- KOLOM KANAN: dipantau, bukan dikerjakan. --}}
  <div class="space-y-4 lg:space-y-5">

    {{-- Kas. Masuk dan keluar berdampingan karena kasir membandingkan
         keduanya saat tutup shift. --}}
    <section class="bg-white border border-paper-300 rounded-lg px-4 sm:px-5 py-4">
      <h2 class="text-xs font-semibold text-paper-500 uppercase tracking-wider">Kas hari ini</h2>
      <dl class="mt-3 space-y-2 text-sm">
        <div class="flex justify-between items-baseline gap-2">
          <dt class="text-paper-500">Masuk</dt>
          <dd class="font-semibold text-emerald-700 tabular-nums">{{ money($stats['cash_income']) }}</dd>
        </div>
        <div class="flex justify-between items-baseline gap-2">
          <dt class="text-paper-500">Keluar</dt>
          <dd class="font-semibold text-rose-700 tabular-nums">{{ money($stats['cash_expense']) }}</dd>
        </div>
      </dl>
      @if($stats['cash_income'] || $stats['cash_expense'])
        <a href="{{ route('cash.index') }}" class="block mt-3 pt-3 border-t border-paper-200 text-sm text-teal-700 hover:underline">Buka kas</a>
      @endif
    </section>

    {{-- Piutang. Amber karena belum uang, bukan error. --}}
    @if($stats['outstanding'] > 0)
    <section class="bg-amber-50 border border-amber-300 rounded-lg px-4 sm:px-5 py-4">
      <h2 class="text-xs font-semibold text-amber-800 uppercase tracking-wider">Belum dibayar</h2>
      <p class="text-2xl font-bold text-amber-900 mt-1.5 tabular-nums">{{ money($stats['outstanding']) }}</p>
      <a href="{{ route('orders.index') }}" class="block mt-3 pt-3 border-t border-amber-200 text-sm text-amber-800 hover:underline">Lihat order</a>
    </section>
    @endif

    {{-- Judul menyebut pertanyaannya, bukan "Overview". --}}
    <section class="bg-white border border-paper-300 rounded-lg px-4 sm:px-5 py-4">
      <h2 class="font-semibold text-paper-800 text-sm">Penjualan 7 hari terakhir</h2>
      @php $__max = $sales7->max('total') ?: 0; @endphp
      @if($__max > 0)
        <ul class="mt-3 space-y-2">
          @foreach($sales7 as $d)
          <li class="flex items-center gap-2.5">
            <span class="text-xs text-paper-500 w-12 shrink-0 tabular-nums">{{ \Carbon\Carbon::parse($d->order_date)->format('d M') }}</span>
            <span class="flex-1 h-2 bg-paper-200 rounded-full overflow-hidden">
              <span class="block h-full bg-teal-600 rounded-full" style="width: {{ round($d->total / $__max * 100) }}%"></span>
            </span>
            <span class="text-xs text-paper-700 w-24 text-right shrink-0 tabular-nums">{{ money($d->total) }}</span>
          </li>
          @endforeach
        </ul>
      @else
        <p class="text-sm text-paper-500 mt-3">Belum ada penjualan dalam 7 hari terakhir.</p>
      @endif
    </section>

    {{-- Status laundry. Daftar, bukan chart: empat kategori, membandingkan
         jumlah. Chart pie butuh usaha lebih besar dari yang dibayar. --}}
    <section class="bg-white border border-paper-300 rounded-lg px-4 sm:px-5 py-4">
      <h2 class="text-xs font-semibold text-paper-500 uppercase tracking-wider">Status laundry</h2>
      @if($byStatus->isNotEmpty())
        <dl class="mt-3 space-y-2 text-sm">
          @foreach($byStatus as $__st => $__cnt)
          <div class="flex justify-between items-baseline gap-2">
            <dt class="text-paper-600">{{ ucfirst(str_replace('_',' ',$__st)) }}</dt>
            <dd class="font-semibold text-paper-800 tabular-nums">{{ $__cnt }}</dd>
          </div>
          @endforeach
        </dl>
      @else
        <p class="text-sm text-paper-500 mt-3">Belum ada order laundry.</p>
      @endif
    </section>
  </div>
</div>
@endsection
