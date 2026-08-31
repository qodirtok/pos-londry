@extends('layouts.app')
@section('title','Laporan payments')
@section('content')
<h1 class="text-xl font-bold mb-4 capitalize">Laporan payments</h1>
<form class="bg-white rounded-2xl border p-4 mb-4 flex gap-2"><input name="from" type="date" value="{{ $from ?? request('from') }}" class="border rounded-xl px-3 py-2"><input name="to" type="date" value="{{ $to ?? request('to') }}" class="border rounded-xl px-3 py-2"><button class="bg-slate-900 text-white px-4 py-2 rounded-xl">Filter</button></form>
<div class="bg-white rounded-2xl border p-4">
@if(isset($summary))<div class="grid grid-cols-3 gap-4 mb-4">@foreach($summary as $k=>$v)<div class="bg-slate-50 rounded-xl p-3"><div class="text-xs text-slate-500 uppercase">{{ $k }}</div><div class="font-bold">{{ is_numeric($v) ? money($v) : $v }}</div></div>@endforeach</div>@endif
@if(isset($perDay))<h3 class="font-semibold mb-2">Per Hari</h3><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="text-left p-2">Tanggal</th><th>Total</th><th>Transaksi</th></tr></thead><tbody>@foreach($perDay as $d)<tr class="border-t"><td class="p-2">{{ $d->order_date }}</td><td>{{ money($d->total) }}</td><td>{{ $d->cnt }}</td></tr>@endforeach</tbody></table>@endif
@if(isset($data))<table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="text-left p-2">Nama</th><th class="text-right p-2">Total</th></tr></thead><tbody>@foreach($data as $d)<tr class="border-t"><td class="p-2">{{ $d->payment_method ?? $d->category ?? $d->name ?? '-' }}</td><td class="text-right p-2">{{ money($d->total ?? $d->revenue ?? 0) }}</td></tr>@endforeach</tbody></table>@endif
@if(isset($perCat))<table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="text-left p-2">Kategori</th><th>Tipe</th><th class="text-right">Total</th></tr></thead><tbody>@foreach($perCat as $d)<tr class="border-t"><td class="p-2">{{ $d->category }}</td><td>{{ $d->type }}</td><td class="text-right">{{ money($d->total) }}</td></tr>@endforeach</tbody></table>@endif
</div>
@endsection
