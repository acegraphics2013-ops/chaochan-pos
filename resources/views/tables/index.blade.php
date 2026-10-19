@extends('layouts.app')
@section('title','Tables & QR Codes')
@section('subtitle','Manage dine-in tables and print unique customer QR codes.')
@section('content')
<div class="info-banner"><span class="info-icon">i</span><div><strong>QR codes use the address below</strong><p>Customer menu links currently point to <strong>{{ $base }}</strong>. Make sure the server is reachable on your network (run <code>php artisan serve --host=0.0.0.0 --port=8000</code>, or use Laragon) and that phones join the same Wi-Fi or hotspot. If this address is wrong, set <code>APP_PUBLIC_URL</code> in <code>.env</code> (e.g. <code>http://192.168.1.38:8000</code>) and reload.</p></div></div>
<section class="panel">
 <div class="section-heading"><h2>Add a Table</h2><span class="muted">Creates the table with its own customer QR code</span></div>
 <form action="{{ route('tables.store') }}" method="post" class="form-row">@csrf<label>Table No.<input name="number" placeholder="09" maxlength="20" required></label><label>Capacity<input type="number" min="1" max="50" name="capacity" value="4" required></label><button class="btn btn-primary">＋ Add Table</button></form>
</section>
<section class="panel">
 <div class="section-heading"><h2>Restaurant Tables</h2><span class="muted">{{ $tables->count() }} registered</span></div>
 @if($tables->isEmpty())
 <p class="empty-state">No tables yet. Add your first table above to generate its QR code.</p>
 @else
 <div class="tables-grid">
 @foreach($tables as $t)
 <article class="table-tile{{ $t->active?'':' is-disabled' }}">
  <div class="tile-head"><span class="table-number">TABLE <strong>{{ $t->number }}</strong></span><span class="pill {{ $t->status==='available'?'green-pill':'orange-pill' }}">{{ ucfirst($t->status) }}</span></div>
  <div class="tile-qr"><img class="qr-image" src="{{ route('tables.qr',$t->id) }}" alt="QR code for table {{ $t->number }}"></div>
  <div class="tile-meta"><span>Seats {{ $t->capacity }}</span>@if(!$t->active)<span class="pill orange-pill">Disabled</span>@endif</div>
  <a class="btn btn-primary full" href="{{ $t->order_url }}" target="_blank" rel="noopener">Open Customer Menu</a>
  <div class="tile-actions">
   <a class="btn btn-soft btn-sm" href="{{ route('tables.qr',$t->id) }}" target="_blank" rel="noopener">Print QR</a>
   <button type="button" class="btn btn-soft btn-sm copy-link" data-link="{{ $t->order_url }}">Copy link</button>
   <button type="button" class="btn btn-soft btn-sm icon-btn edit-table" title="Edit table" aria-label="Edit table {{ $t->number }}" data-id="{{ $t->id }}" data-number="{{ $t->number }}" data-capacity="{{ $t->capacity }}" data-active="{{ $t->active?1:0 }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></button>
   <form method="post" action="{{ route('tables.destroy',$t->id) }}" onsubmit="return confirm('Delete table {{ $t->number }}? This cannot be undone.')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm icon-btn" title="Delete table" aria-label="Delete table {{ $t->number }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button></form>
  </div>
  <form method="post" action="{{ route('tables.close',$t->id) }}" onsubmit="return confirm('Close this table visit? This checks for pending orders and unpaid bills.')">@csrf<button type="submit" class="btn btn-soft btn-sm full">Close / Reset Table</button></form>
 </article>
 @endforeach
 </div>
 @endif
</section>
<div id="edit-table-modal" class="modal-screen hidden" role="dialog" aria-modal="true" aria-labelledby="edit-table-title"><div class="modal-card"><div class="section-heading"><h2 id="edit-table-title">Edit Table</h2><button type="button" id="edit-table-close" class="btn btn-soft">✕</button></div><form method="post" id="edit-table-form">@csrf @method('PUT')<label>Table No.<input name="number" id="et-number" maxlength="20" required></label><label>Capacity<input type="number" name="capacity" id="et-capacity" min="1" max="50" required></label><label class="checkline"><input type="checkbox" name="active" id="et-active" value="1"> Active table</label><button class="btn btn-primary full" type="submit">Save Changes</button></form></div></div>
@endsection
@push('scripts')
<script>
(function(){
 var modal=document.getElementById('edit-table-modal'),form=document.getElementById('edit-table-form');
 if(modal){
  var base='{{ url('tables') }}/';
  document.querySelectorAll('.edit-table').forEach(function(btn){btn.addEventListener('click',function(){
   form.action=base+btn.dataset.id;
   document.getElementById('et-number').value=btn.dataset.number||'';
   document.getElementById('et-capacity').value=btn.dataset.capacity||4;
   document.getElementById('et-active').checked=btn.dataset.active==='1';
   modal.classList.remove('hidden');
  });});
  document.getElementById('edit-table-close').addEventListener('click',function(){modal.classList.add('hidden');});
  modal.addEventListener('click',function(e){if(e.target===modal)modal.classList.add('hidden');});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')modal.classList.add('hidden');});
 }
 document.querySelectorAll('.copy-link').forEach(function(btn){
  btn.addEventListener('click',function(){
   var link=btn.dataset.link;if(!link)return;
   var done=function(){var label=btn.getAttribute('data-label')||btn.textContent;btn.setAttribute('data-label',label);btn.textContent='Copied!';btn.classList.add('copied');setTimeout(function(){btn.textContent=label;btn.classList.remove('copied');},1500);};
   var fallback=function(){var ta=document.createElement('textarea');ta.value=link;ta.style.position='fixed';ta.style.opacity='0';document.body.appendChild(ta);ta.select();try{document.execCommand('copy');done();}catch(e){}document.body.removeChild(ta);};
   if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(link).then(done).catch(fallback);}else{fallback();}
  });
 });
})();
</script>
@endpush
