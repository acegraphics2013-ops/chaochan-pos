@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle','Your ChaoChan Fried Rice business at a glance.')
@section('content')
@php
 $role=auth()->user()->role;
 $can=fn(string $m)=>\App\Support\Permissions::allows($role,$m);
 $primary=$can('pos')?['pos.index','▣','Open Point of Sale']:($can('kitchen')?['kitchen.index','♨','Open Kitchen Display']:($can('orders')?['orders.index','☷','Review Orders']:null));
@endphp
<div class="dash">
<div class="hero-strip"><div><small>{{ strtoupper(now()->format('l, F j')) }}</small><h2>Good day, {{ explode(' ',auth()->user()->name)[0] }}!</h2><p>Here is how ChaoChan is doing today.</p>@if($primary)<a class="hero-cta" href="{{ route($primary[0]) }}">{{ $primary[1] }} {{ $primary[2] }} →</a>@endif</div><div class="hero-tag">PALDO NA!<br><strong>BUSOG PA!</strong></div></div>
@if($pending>0||$openBills>0||$lowStock>0)
<div class="attention-strip">
@if($pending>0&&$can('orders'))<a href="{{ route('orders.index') }}">⚠ {{ $pending }} QR order{{ $pending===1?'':'s' }} awaiting confirmation</a>@endif
@if($openBills>0&&$can('orders'))<a href="{{ route('orders.index') }}">₱ {{ $openBills }} unpaid bill{{ $openBills===1?'':'s' }}</a>@endif
@if($lowStock>0&&$can('inventory'))<a href="{{ route('inventory.index') }}">◫ {{ $lowStock }} inventory item{{ $lowStock===1?'':'s' }} at reorder level</a>@endif
</div>
@endif
<div class="stats-grid"><article class="stat-card"><div class="stat-icon green">₱</div><small>Today's Collected Payments</small><strong>₱{{ number_format($todaySales,2) }}</strong><span>Cash & GCash combined</span></article><article class="stat-card"><div class="stat-icon blue">▣</div><small>Today's Orders</small><strong>{{ $orderCount }}</strong><span>From POS and QR</span></article><article class="stat-card"><div class="stat-icon orange">◷</div><small>Pending QR Confirmation</small><strong>{{ $pending }}</strong><span>Requires cashier review</span></article><article class="stat-card"><div class="stat-icon red">♨</div><small>Active Kitchen Tickets</small><strong>{{ $kitchen }}</strong><span>{{ $openBills }} outstanding bills</span></article></div>
<div class="two-col"><section class="panel"><div class="section-heading"><h2>Recent Orders</h2>@if($can('orders'))<a href="{{ route('orders.index') }}">View all →</a>@endif</div><div class="table-wrap"><table><thead><tr><th>Order</th><th>Table</th><th>Amount</th><th>Mode</th><th>Status</th></tr></thead><tbody>@forelse($recent as $o)@php $cls=in_array($o->status,['accepted','completed'])?'green-pill':(in_array($o->status,['pending_confirmation','awaiting_payment'])?'orange-pill':''); @endphp<tr><td><strong>{{ $o->number }}</strong></td><td>{{ $o->table_number ?? 'Take-out' }}</td><td>₱{{ number_format($o->total,2) }}</td><td>{{ str_replace('_',' ',ucwords($o->payment_mode)) }}</td><td><span class="pill {{ $cls }}">{{ str_replace('_',' ',$o->status) }}</span></td></tr>@empty<tr><td colspan="5"><p class="empty-state">No orders yet today.</p></td></tr>@endforelse</tbody></table></div></section><section class="panel"><div class="section-heading"><h2>Best-Selling Menu</h2>@if($can('menu'))<a href="{{ route('menu.index') }}">Manage menu →</a>@endif</div><div class="bestsellers">@forelse($top as $i=>$p)<div class="best-row"><span class="rank">{{ $i+1 }}</span><span class="best-food">🍳</span><span class="grow">{{ $p->name_snapshot }}</span><strong>{{ $p->qty }} sold</strong></div>@empty<p class="empty-state">Best sellers appear after orders are accepted.</p>@endforelse</div></section></div>
<div class="section-heading"><h2>Shortcuts</h2></div>
<div class="quick-links">
@if($can('pos'))<a href="{{ route('pos.index') }}">▣ New Cashier Order</a>@endif
@if($can('orders'))<a href="{{ route('orders.index') }}">☷ Review QR Orders</a>@endif
@if($can('tables'))<a href="{{ route('tables.index') }}">▦ Print Table QR Codes</a>@endif
@if($can('menu'))<a href="{{ route('menu.index') }}">☰ Manage Menu</a>@endif
@if($can('kitchen'))<a href="{{ route('kitchen.index') }}">♨ Kitchen Display</a>@endif
@if($can('sales'))<a href="{{ route('sales.index') }}">▥ Sales Monitoring</a>@endif
@if($can('inventory'))<a href="{{ route('inventory.index') }}">◫ Inventory</a>@endif
</div>
</div>
@endsection
