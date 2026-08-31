<!DOCTYPE html><html><head><meta charset="utf-8"><title>Struk {{ $order->order_number }}</title><style>body{font-family:monospace;max-width:320px;margin:0 auto;padding:16px;font-size:13px} h2,h3{margin:4px 0;text-align:center} hr{border:none;border-top:1px dashed #000;margin:8px 0} .right{text-align:right} .center{text-align:center} table{width:100%} td{vertical-align:top}</style></head><body>
<h3>{{ setting('company_name','Londry') }}</h3><div class="center">{{ $order->branch->name }}<br>{{ $order->branch->address }}<br>{{ $order->branch->phone }}</div>
<hr>
<div>No: {{ $order->order_number }}<br>Tgl: {{ $order->order_date->format('d/m/Y H:i') }}<br>Kasir: {{ $order->cashier->name }}<br>Cust: {{ $order->customer->name ?? 'Walk-in' }} {{ $order->customer->phone ?? '' }}</div>
<hr>
@foreach($order->items as $it)
<div>{{ $it->product_name }}<br>{{ $it->quantity }} {{ $it->unit }} x {{ money($it->price) }} <span class="right" style="float:right">{{ money($it->subtotal) }}</span></div>
@endforeach
@if(!empty($order->laundry_details))
<div style="border:1px dashed #000;padding:6px 8px;margin:6px 0;font-size:12px">
<div style="text-align:center;font-weight:bold;margin-bottom:4px">RINCIAN LAUNDRY</div>
@php $d=$order->laundry_details; $map=[]; try{ $codes=array_keys(array_filter($d, fn($k)=>!in_array($k,['catatan','lainnya_desc']), ARRAY_FILTER_USE_KEY)); $map=\App\Models\LaundryItemType::whereIn('code',$codes)->pluck('name','code')->toArray(); }catch(\Throwable $e){} @endphp
@foreach($d as $k=>$v) @if(!in_array($k,['catatan','lainnya_desc']) && !empty($v))<div>{{ $map[$k] ?? ucfirst(str_replace('_',' ',$k)) }}: {{ $v }} pcs</div>@endif @endforeach
@if(!empty($d['lainnya_desc']))<div>Ket. Lainnya: {{ $d['lainnya_desc'] }}</div>@endif
@if(!empty($d['catatan']))<div>Catatan: {{ $d['catatan'] }}</div>@endif
</div>
@endif
<hr>
<table><tr><td>Subtotal</td><td class="right">{{ money($order->subtotal) }}</td></tr><tr><td>Diskon</td><td class="right">{{ money($order->discount) }}</td></tr><tr><td>Pajak</td><td class="right">{{ money($order->tax) }}</td></tr><tr><td><b>TOTAL</b></td><td class="right"><b>{{ money($order->total) }}</b></td></tr><tr><td>Bayar</td><td class="right">{{ money($order->paid_amount) }}</td></tr><tr><td>Kembalian</td><td class="right">{{ money($order->change_amount) }}</td></tr></table>
<hr>
<div class="center">{{ setting('receipt_footer','Terima kasih') }}<br><small>Status: {{ $order->order_status }} • {{ $order->payment_status }}</small></div>
<div class="center" style="margin-top:12px"><button onclick="window.print()" style="padding:8px 16px">Cetak</button></div>
</body></html>
