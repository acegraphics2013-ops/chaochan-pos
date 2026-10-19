@extends('layouts.app')
@section('title','Menu Management')
@section('subtitle','Manage the ChaoChan food menu, prices and availability.')
@section('content')
<div class="stats-grid">
 <article class="stat-card"><div class="stat-icon green">☰</div><small>Menu Items</small><strong>{{ count($products) }}</strong><span>Across {{ count($categories) }} categories</span></article>
 <article class="stat-card"><div class="stat-icon blue">✓</div><small>Available</small><strong>{{ $products->where('available',1)->count() }}</strong><span>Orderable on POS & QR</span></article>
 <article class="stat-card"><div class="stat-icon orange">◌</div><small>Hidden</small><strong>{{ $products->where('available',0)->count() }}</strong><span>Not shown to customers</span></article>
 <article class="stat-card"><div class="stat-icon red">₱</div><small>Average Price</small><strong>₱{{ number_format($products->avg('price') ?: 0,2) }}</strong><span>Per menu item</span></article>
</div>
<div class="two-col menu-admin-layout">
 <section class="panel">
  <div class="section-heading"><h2>Food & Drinks <span class="muted">({{ count($products) }})</span></h2></div>
  <div class="list-toolbar">
   <label class="searchbox">⌕ <input id="menu-search" placeholder="Search name, SKU or category..." autocomplete="off"></label>
   <div class="toggle-group compact" id="menu-filter">
    <button type="button" class="active" data-filter="all">All</button>
    <button type="button" data-filter="active">Active</button>
    <button type="button" data-filter="hidden">Hidden</button>
   </div>
  </div>
  <div class="table-wrap"><table><thead><tr><th>Menu Item</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead><tbody id="menu-table-body">
  @forelse($products as $p)
   <tr class="menu-row" data-search="{{ strtolower($p->sku.' '.$p->name.' '.$p->category) }}" data-available="{{ $p->available?1:0 }}">
    <td><div class="menu-cell">@if($p->image_path)<img class="menu-thumb" src="{{ asset('storage/'.$p->image_path) }}" alt="">@else<span class="menu-thumb placeholder">🍳</span>@endif<div><span class="sku-text">{{ $p->sku }}</span><strong>{{ $p->name }}</strong></div></div></td>
    <td>{{ $p->category }}</td>
    <td>₱{{ number_format($p->price,2) }}</td>
    <td><span class="pill {{ $p->available?'green-pill':'orange-pill' }}">{{ $p->available?'Active':'Hidden' }}</span></td>
    <td><div class="row-actions"><button type="button" class="btn btn-soft btn-sm icon-btn edit-product" title="Edit item" aria-label="Edit {{ $p->name }}" data-id="{{ $p->id }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></button><form method="post" action="{{ route('menu.destroy',$p->id) }}" class="delete-product-form" data-name="{{ $p->name }}">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm icon-btn" title="Delete item" aria-label="Delete {{ $p->name }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button></form></div></td>
   </tr>
  @empty
   <tr><td colspan="5"><p class="empty-state">No menu items yet. Add your first item using the form on the right.</p></td></tr>
  @endforelse
  </tbody></table></div>
  <p id="menu-empty" class="empty-state hidden">No menu items match your search or filter.</p>
 </section>
 <section class="panel menu-form-panel">
  <div class="section-heading"><h2 id="product-form-heading">Add Menu Item</h2><button type="button" class="btn btn-soft btn-sm" id="reset-product">Reset</button></div>
  <form id="product-editor" method="post" enctype="multipart/form-data" action="{{ route('menu.store') }}">
   @csrf<div id="method-slot"></div>
   <label>Item Code<input required name="sku" id="menu-sku" maxlength="40" placeholder="H6"></label>
   <label>Item Name<input required name="name" id="menu-name" maxlength="200"></label>
   <label>Category<select name="category_id" id="menu-category">@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
   <label>Selling Price (₱)<input type="number" required name="price" id="menu-price" min="0.01" step="0.01"></label>
   <label>Description<textarea name="description" id="menu-description" rows="2"></textarea></label>
   <label>Product Image<input type="file" name="image" accept="image/*"></label>
   <label class="checkline"><input id="menu-available" name="available" type="checkbox" value="1" checked> Available for ordering</label>
   <button class="btn btn-primary full" type="submit" id="save-product">Save Menu Item</button>
  </form>
 </section>
</div>
<script id="products-admin-data" type="application/json">@json($products)</script>
@endsection
@push('scripts')<script src="{{ asset('js/menu.js') }}" defer></script>@endpush
