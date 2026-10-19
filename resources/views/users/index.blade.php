@extends('layouts.app')
@section('title','User Accounts')
@section('subtitle','Employee accounts and role-based permissions.')
@section('content')<div class="two-col"><section class="panel"><div class="section-heading"><h2>Current Staff</h2><a class="btn btn-soft btn-sm" href="{{ route('settings.permissions') }}">Module Access</a></div><div class="table-wrap"><table><thead><tr><th>Name / Username</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody>@foreach($users as $u)<tr><td><strong>{{ $u->name }}</strong><br><small>{{ $u->username }}</small>@if($u->email)<br><small>{{ $u->email }}</small>@endif</td><td>{{ ucfirst($u->role) }}</td><td><span class="pill {{ $u->active?'green-pill':'orange-pill' }}">{{ $u->active?'Active':'Disabled' }}</span></td><td><div class="row-actions"><button type="button" class="btn btn-soft btn-sm icon-btn edit-user" title="Edit account" aria-label="Edit {{ $u->name }}" data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-username="{{ $u->username }}" data-email="{{ $u->email }}" data-role="{{ $u->role }}" data-active="{{ $u->active?1:0 }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></button><form method="post" action="{{ route('users.destroy',$u->id) }}" onsubmit="return confirm('Delete {{ $u->name }}? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm icon-btn" type="submit" title="Delete account" aria-label="Delete {{ $u->name }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button></form></div></td></tr>@endforeach</tbody></table></div></section><section class="panel"><h2>Add Employee</h2><form method="post" action="{{ route('users.store') }}">@csrf<label>Full Name<input name="name" required></label><label>Username<input name="username" required></label><label>Email (optional)<input name="email" type="email"></label><label>Password (12+ characters)<input name="password" type="password" minlength="12" required></label><label>Role<select name="role"><option value="cashier">Cashier</option><option value="kitchen">Kitchen</option><option value="manager">Manager</option><option value="admin">Admin</option></select></label><label class="checkline"><input type="checkbox" name="active" value="1" checked> Active account</label><button class="btn btn-primary full">Create Employee Account</button></form></section></div>
<div id="edit-user-modal" class="modal-screen hidden" role="dialog" aria-modal="true" aria-labelledby="edit-user-title"><div class="modal-card"><div class="section-heading"><h2 id="edit-user-title">Edit Account</h2><button type="button" id="edit-user-close" class="btn btn-soft">✕</button></div><form method="post" id="edit-user-form">@csrf @method('PUT')<label>Full Name<input name="name" id="eu-name" required></label><label>Username<input name="username" id="eu-username" required></label><label>Email (optional)<input name="email" type="email" id="eu-email"></label><label>New Password (leave blank to keep current)<input name="password" type="password" id="eu-password" minlength="12" autocomplete="new-password"></label><label>Role<select name="role" id="eu-role"><option value="cashier">Cashier</option><option value="kitchen">Kitchen</option><option value="manager">Manager</option><option value="admin">Admin</option></select></label><label class="checkline"><input type="checkbox" name="active" id="eu-active" value="1"> Active account</label><button class="btn btn-primary full" type="submit">Save Changes</button></form></div></div>
@endsection
@push('scripts')
<script>
(function(){
 var modal=document.getElementById('edit-user-modal'),form=document.getElementById('edit-user-form');
 if(!modal)return;
 var base='{{ url('users') }}/';
 document.querySelectorAll('.edit-user').forEach(function(btn){btn.addEventListener('click',function(){
  form.action=base+btn.dataset.id;
  document.getElementById('eu-name').value=btn.dataset.name||'';
  document.getElementById('eu-username').value=btn.dataset.username||'';
  document.getElementById('eu-email').value=btn.dataset.email||'';
  document.getElementById('eu-password').value='';
  document.getElementById('eu-role').value=btn.dataset.role||'cashier';
  document.getElementById('eu-active').checked=btn.dataset.active==='1';
  modal.classList.remove('hidden');
 });});
 document.getElementById('edit-user-close').addEventListener('click',function(){modal.classList.add('hidden');});
 modal.addEventListener('click',function(e){if(e.target===modal)modal.classList.add('hidden');});
 document.addEventListener('keydown',function(e){if(e.key==='Escape')modal.classList.add('hidden');});
})();
</script>
@endpush
