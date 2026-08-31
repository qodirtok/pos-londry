@extends('layouts.app')
@section('title','Settings')
@section('content')
<h1 class="text-xl font-bold mb-4">Settings</h1>
<form method="POST" action="{{ route('settings.update') }}" class="bg-white rounded-2xl border p-6 space-y-4 max-w-2xl">@csrf
@foreach(['app_name'=>'Nama Aplikasi','company_name'=>'Nama Perusahaan','currency'=>'Currency','receipt_header'=>'Header Struk','receipt_footer'=>'Footer Struk'] as $k=>$label)
<div><label class="text-sm font-medium">{{ $label }} ({{ $k }})</label><input name="{{ $k }}" value="{{ $settings[$k]->value ?? '' }}" class="mt-1 w-full border rounded-xl px-3 py-2"></div>
@endforeach
<button class="bg-indigo-600 text-white px-6 py-2 rounded-xl">Simpan</button>
</form>
<p class="text-xs text-slate-500 mt-4">WhatsApp, receipt size, dsb bisa ditambah di table settings.</p>
@endsection
