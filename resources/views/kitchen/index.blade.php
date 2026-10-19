@extends('layouts.app')
@section('title','Kitchen Display')
@section('subtitle','Incoming confirmed meals • New → Preparing → Ready → Served')
@section('content')<div class="kds-banner"><span class="live-dot"></span> LIVE KITCHEN BOARD <button class="btn btn-soft" id="kitchen-fullscreen">⛶ Full Screen</button><button class="btn btn-soft" id="kitchen-refresh">↻ Refresh</button><span class="muted">Auto-refresh every 4 seconds</span></div><section id="kitchen-board" class="kitchen-grid" data-feed="{{ route('kitchen.feed') }}"><p class="empty-state">Loading kitchen tickets…</p></section>
@endsection
@push('scripts')<script src="{{ asset('js/kitchen.js') }}" defer></script>@endpush
