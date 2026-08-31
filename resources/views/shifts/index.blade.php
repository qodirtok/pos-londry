@extends('layouts.app')
@section('title','Shift Kasir')
@section('content')
<h1 class="text-xl font-bold mb-4">Shift Kasir</h1>
@if(!$open)
<div class="bg-white rounded-2xl border p-4 mb-4">
<h3 class="font-semibold mb-2">Buka Shift</h3>
<form method="POST" action="{{ route('shifts.open') }}" class="flex gap-2">@csrf<input name="opening_cash" type="number" placeholder="Modal awal" required class="flex-1 border rounded-xl px-3 py-2"><input name="notes" placeholder="Catatan" class="flex-1 border rounded-xl px-3 py-2"><button class="bg-indigo-600 text-white px-4 py-2 rounded-xl">Buka</button></form>
</div>
@else
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4">
<div class="font-semibold">Shift Aktif • Buka {{ $open->opened_at->format('d/m/Y H:i') }} • Modal: {{ money($open->opening_cash) }}</div>
<form method="POST" action="{{ route('shifts.close',$open) }}" class="flex gap-2 mt-2">@csrf<input name="actual_cash" type="number" placeholder="Uang fisik saat tutup" required class="flex-1 border rounded-xl px-3 py-2"><button class="bg-slate-900 text-white px-4 py-2 rounded-xl">Tutup Shift</button></form>
</div>
@endif
<div class="bg-white rounded-2xl border overflow-hidden"><table class="w-full text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="text-left p-3">Kasir</th><th>Buka</th><th>Tutup</th><th>Modal</th><th>Expected</th><th>Actual</th><th>Selisih</th><th>Status</th></tr></thead><tbody>@foreach($shifts as $s)<tr class="border-t"><td class="p-3">{{ $s->cashier->name }}</td><td class="text-xs">{{ $s->opened_at->format('d/m H:i') }}</td><td class="text-xs">{{ $s->closed_at? $s->closed_at->format('d/m H:i'):'-' }}</td><td>{{ money($s->opening_cash) }}</td><td>{{ $s->expected_cash? money($s->expected_cash):'-' }}</td><td>{{ $s->actual_cash? money($s->actual_cash):'-' }}</td><td class="{{ ($s->difference??0)==0?'text-slate-500':($s->difference>0?'text-emerald-600':'text-rose-600') }}">{{ $s->difference!==null? money($s->difference):'-' }}</td><td><span class="px-2 py-1 rounded-full text-xs {{ $s->status=='open'?'bg-emerald-100 text-emerald-700':'bg-slate-100' }}">{{ $s->status }}</span></td></tr>@endforeach</tbody></table><div class="p-3">{{ $shifts->links() }}</div></div>
@endsection
