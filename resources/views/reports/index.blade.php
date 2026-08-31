@extends('layouts.app')
@section('title','Laporan')
@section('content')
<h1 class="text-xl font-bold mb-4">Laporan</h1>
<div class="grid md:grid-cols-3 gap-4">
<a href="{{ route('reports.sales') }}" class="bg-white rounded-2xl border p-6 hover:shadow">📈<div class="font-semibold mt-2">Penjualan</div><div class="text-sm text-slate-500">Total transaksi, gross/net</div></a>
<a href="{{ route('reports.payments') }}" class="bg-white rounded-2xl border p-6 hover:shadow">💳<div class="font-semibold mt-2">Pembayaran</div><div class="text-sm text-slate-500">Per metode bayar</div></a>
<a href="{{ route('reports.cash') }}" class="bg-white rounded-2xl border p-6 hover:shadow">💰<div class="font-semibold mt-2">Kas</div><div class="text-sm text-slate-500">Income/expense</div></a>
<a href="{{ route('reports.products') }}" class="bg-white rounded-2xl border p-6 hover:shadow">📦<div class="font-semibold mt-2">Produk</div><div class="text-sm text-slate-500">Qty & revenue</div></a>
<a href="{{ route('reports.customers') }}" class="bg-white rounded-2xl border p-6 hover:shadow">👥<div class="font-semibold mt-2">Customer</div><div class="text-sm text-slate-500">Top spender</div></a>
<a href="{{ route('reports.laundry') }}" class="bg-white rounded-2xl border p-6 hover:shadow">👕<div class="font-semibold mt-2">Laundry</div><div class="text-sm text-slate-500">Status & weight</div></a>
</div>
@endsection
