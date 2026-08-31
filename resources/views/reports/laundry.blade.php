@extends('layouts.app')
@section('title','Laporan Laundry')
@section('content')
<h1 class="text-xl font-bold mb-4">Laporan Laundry</h1>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
@foreach($summary as $k=>$v)<div class="bg-white rounded-2xl border p-4"><div class="text-xs uppercase text-slate-500">{{ $k }}</div><div class="font-bold text-lg">{{ is_numeric($v) && $k!='total' && $k!='pending' && $k!='ready' && $k!='picked' && $k!='cancelled' ? (str_contains($k,'weight') ? $v.' kg' : money($v)) : $v }}</div></div>@endforeach
</div>
<div class="bg-white rounded-2xl border p-4"><h3 class="font-semibold mb-2">Per Status</h3><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="text-left p-2">Status</th><th>Jumlah</th></tr></thead><tbody>@foreach($byStatus as $s=>$cnt)<tr class="border-t"><td class="p-2 capitalize">{{ str_replace('_',' ',$s) }}</td><td>{{ $cnt }}</td></tr>@endforeach</tbody></table></div>
@endsection
