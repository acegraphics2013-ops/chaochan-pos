@extends('layouts.app')
@section('title','Module Access')
@section('subtitle','Configure which modules each role can open.')
@section('content')
<div class="panel">
 <div class="section-heading"><h2>Role / Module Permissions</h2><a class="btn btn-soft btn-sm" href="{{ route('users.index') }}">Back to User Accounts</a></div>
 <p class="muted">Administrators always have full access and cannot be restricted. Changes apply immediately to the sidebar and route guards after saving.</p>
 <form method="post" action="{{ route('settings.permissions.update') }}">
  @csrf
  <div class="table-wrap"><table><thead><tr><th>Module</th>@foreach($roles as $role)<th>{{ ucfirst($role) }}</th>@endforeach<th>Admin</th></tr></thead><tbody>
  @foreach($modules as $key=>$label)
   <tr><td><strong>{{ $label }}</strong><br><small class="muted">{{ $key }}</small></td>
   @foreach($roles as $role)
    <td><input type="checkbox" name="permissions[{{ $role }}][{{ $key }}]" value="1" {{ !empty($matrix[$role][$key])?'checked':'' }} aria-label="{{ $label }} for {{ $role }}"></td>
   @endforeach
   <td><input type="checkbox" checked disabled aria-label="Admin always has full access"> <small class="muted">Full</small></td></tr>
  @endforeach
  </tbody></table></div>
  <button class="btn btn-primary" type="submit">Save Module Access</button>
 </form>
</div>
@endsection
