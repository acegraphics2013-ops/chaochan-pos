<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Dashboard') — ChaoChan POS</title><link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}"><link rel="icon" href="{{ asset('images/chaochan-logo.png') }}"></head>
<body class="app-body{{ request()->routeIs('dashboard')?' dash-mode':'' }}">
<aside class="sidebar" id="sidebar"><div class="brand"><img src="{{ asset('images/chaochan-logo.png') }}" alt="ChaoChan Fried Rice logo"><strong>ChaoChan <span>Fried Rice</span></strong><small>Good Food · Good Mood</small></div>
<nav class="side-links">
@php $role=auth()->user()->role; $can=fn(string $m)=>\App\Support\Permissions::allows($role,$m); @endphp
<a class="{{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">▦ <span>Dashboard</span></a>
@if($can('pos'))<a class="{{ request()->routeIs('pos.*')?'active':'' }}" href="{{ route('pos.index') }}">▣ <span>Point of Sale</span></a>@endif
@if($can('orders'))<a class="{{ request()->routeIs('orders.*')?'active':'' }}" href="{{ route('orders.index') }}">☷ <span>Live Orders</span></a>@endif
@if($can('shifts'))<a class="{{ request()->routeIs('shifts.*')?'active':'' }}" href="{{ route('shifts.index') }}">◷ <span>My Cashier Shift</span></a>@endif
@if($can('kitchen'))<a class="{{ request()->routeIs('kitchen.*')?'active':'' }}" href="{{ route('kitchen.index') }}">♨ <span>Kitchen Display</span></a>@endif
@if($can('tables'))<a class="{{ request()->routeIs('tables.*')?'active':'' }}" href="{{ route('tables.index') }}">▦ <span>Tables & QR</span></a>@endif
@if($can('menu'))<a class="{{ request()->routeIs('menu.*')?'active':'' }}" href="{{ route('menu.index') }}">☰ <span>Menu Management</span></a>@endif
@if($can('sales'))<a class="{{ request()->routeIs('sales.*')?'active':'' }}" href="{{ route('sales.index') }}">▥ <span>Sales Monitoring</span></a>@endif
@if($can('expenses'))<a class="{{ request()->routeIs('expenses.*')?'active':'' }}" href="{{ route('expenses.index') }}">₱ <span>Expenses</span></a>@endif
@if($can('inventory'))<a class="{{ request()->routeIs('inventory.*')?'active':'' }}" href="{{ route('inventory.index') }}">◫ <span>Inventory</span></a>@endif
@if($role==='admin')<a class="{{ request()->routeIs('users.*')?'active':'' }}" href="{{ route('users.index') }}">♙ <span>User Accounts</span></a><a class="{{ request()->routeIs('settings.*')?'active':'' }}" href="{{ route('settings.permissions') }}">⚙ <span>Module Access</span></a>@endif
</nav>
<div class="side-bottom"><span class="user-circle">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst($role) }}</small></div><form method="post" action="{{ route('logout') }}">@csrf<button aria-label="Log out" class="logout" type="submit">↪</button></form></div></aside>
<div class="app-main"><header class="app-top"><button type="button" class="menu-toggle" id="sidebarToggle" aria-label="Toggle menu">☰</button><div><small class="overline">CHAOCHAN RESTAURANT MANAGEMENT</small><h1>@yield('title','Dashboard')</h1><p class="subtitle">@yield('subtitle','Good food, good mood.')</p></div><div class="top-right"><span class="live-dot"></span> Local Network <span class="top-user">{{ auth()->user()->username }}</span></div></header>
<main class="content">@if(session('success'))<div class="flash flash-good">{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash flash-bad">{{ session('error') }}</div>@endif
@if($errors->any())<div class="flash flash-bad"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@yield('content')</main>
<footer class="footer">ChaoChan Fried Rice · Offline Restaurant POS · Local LAN</footer></div>
<script src="{{ asset('js/app.js') }}" defer></script>@stack('scripts')
</body></html>
