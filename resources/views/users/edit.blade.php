@extends('layouts.app')

@section('content')
<h3>Edit User</h3>
<form method="POST" action="{{ route('users.update', $user) }}">
  @csrf @method('PUT')
  <div class="mb-3"><label>Nom</label><input name="nom" value="{{ $user->nom }}" class="form-control"></div>
  <div class="mb-3"><label>Email</label><input name="email" value="{{ $user->email }}" class="form-control"></div>
  <div class="mb-3"><label>Password (leave blank to keep)</label><input name="password" type="password" class="form-control"></div>
  <div class="mb-3"><label>Confirm Password</label><input name="password_confirmation" type="password" class="form-control"></div>
  <div class="mb-3"><label>Role</label><select name="role" class="form-control"><option value="user" {{ $user->role=='user'?'selected':'' }}>user</option><option value="superadmin" {{ $user->role=='superadmin'?'selected':'' }}>superadmin</option></select></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
