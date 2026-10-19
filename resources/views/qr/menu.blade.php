<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>Table {{ $table->number }} · ChaoChan Menu</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="qr-body">
<header class="qr-header"><img src="{{ asset('images/chaochan-logo.png') }}" alt="ChaoChan logo"><div><strong>ChaoChan Fried Rice</strong><span>Good Food. Good Mood.</span></div><span class="qr-table-label">TABLE {{ $table->number }}</span></header>
<main class="qr-container">
 <div class="qr-greeting"><small>PALDO NA, BUSOG PA!</small><h1>What are you craving?</h1><p>Order from your table — no account needed. Tap an item to add it, then send your order at the bottom.</p></div>
 <div id="order-app" data-mode="qr" data-submit="{{ route('qr.submit',$token) }}" data-track-prefix="{{ url('/q/'.$token.'/track') }}" data-orders-feed="{{ route('qr.orders',$token) }}">
  <div class="qr-toolbar">
   <div class="category-tabs" id="category-tabs"><button class="category-btn active" data-category="all">All</button>@foreach($categories as $c)<button class="category-btn" data-category="{{ $c->id }}">{{ $c->name }}</button>@endforeach</div>
   <label class="searchbox">⌕ <input id="product-search" placeholder="Search ChaoChan menu" autocomplete="off"></label>
  </div>
  <div class="food-grid qr-food-grid" id="products-grid">@foreach($products as $p)<button class="food-card" data-product-id="{{ $p->id }}" data-category="{{ $p->category_id }}" data-search="{{ strtolower($p->name.' '.$p->sku) }}"><div class="food-photo"><img src="{{ $p->image_path?asset('storage/'.$p->image_path):asset('images/food-meal.jpg') }}" alt="{{ $p->name }}"><span class="sku-tag">{{ $p->sku }}</span><span class="food-qty" hidden>0</span></div><div class="food-title">{{ $p->name }}</div><strong>₱{{ number_format($p->price,2) }}</strong><span class="add-tag">＋ Add</span></button>@endforeach</div>
   <p class="empty-state hidden" id="products-empty">No dishes match your search.</p>
  <div class="qr-sheet hidden" id="qr-cart" role="dialog" aria-modal="true" aria-labelledby="qr-cart-title">
   <div class="qr-sheet-panel">
    <div class="qr-sheet-head"><h2 id="qr-cart-title">Your Order <span class="qr-sheet-table">TABLE {{ $table->number }}</span></h2><button type="button" id="qr-cart-close" class="qr-sheet-close" aria-label="Close order">✕</button></div>
    <div id="cart-items" class="cart-items"><p class="empty-state">Choose something delicious above.</p></div>
    <label>Special instructions<textarea id="order-notes" maxlength="500" rows="2" placeholder="Any special requests?"></textarea></label>
    <div class="cart-total"><span>Order total</span><strong id="cart-total">₱0.00</strong></div>
    <h3>When would you like to pay?</h3>
    <div class="toggle-group"><button type="button" class="payment-mode active" data-payment="pay_first">Pay First (at cashier)</button><button type="button" class="payment-mode" data-payment="pay_later">Pay Later</button></div>
    <button id="submit-order" class="btn btn-primary full">Place My Order →</button>
    <button type="button" id="view-orders" class="btn btn-soft full">📡 Track My Orders <span id="orders-count" class="pill">0</span></button>
    <div class="helptext" id="order-status" role="status">Your order will be reviewed by the cashier before cooking.</div>
   </div>
  </div>
 </div>
</main>
<div class="qr-cartbar" id="cartbar" hidden><div class="qr-cartbar-info"><strong id="cartbar-count">0 items</strong><span id="cartbar-total">₱0.00</span></div><button type="button" class="btn btn-primary" id="cartbar-view">View Order →</button></div>
<button type="button" class="qr-track-fab" id="track-fab" hidden>📡 Track My Order <span id="track-fab-count" class="pill">0</span></button>
<div id="orders-modal" class="modal-screen hidden" role="dialog" aria-modal="true" aria-labelledby="orders-modal-title"><div class="modal-card track-card"><div class="section-heading"><h2 id="orders-modal-title">Track Your Order <span class="qr-sheet-table">TABLE {{ $table->number }}</span></h2><button type="button" id="orders-close" class="btn btn-soft">✕</button></div><div class="track-live"><span class="track-dot"></span> Live status · updated <span id="track-updated">—</span></div><div id="orders-list" class="orders-list"><p class="empty-state">Loading your orders…</p></div></div></div>
<footer class="footer">Connected to ChaoChan's local restaurant Wi-Fi</footer>
<script id="product-data" type="application/json">@json($products)</script>
<script src="{{ asset('js/app.js') }}" defer></script>
<script src="{{ asset('js/order-ui.js') }}?v={{ filemtime(public_path('js/order-ui.js')) }}" defer></script>
</body>
</html>
