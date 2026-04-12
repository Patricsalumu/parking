@extends('layouts.app')

@section('content')
<h3>Create User</h3>
<form method="POST" action="{{ route('users.store') }}">
  @csrf
  <div class="mb-3"><label>Nom</label><input name="nom" class="form-control"></div>
  <div class="mb-3"><label>Email</label><input name="email" class="form-control"></div>
  <div class="mb-3"><label>Password</label><input name="password" type="password" class="form-control"></div>
  <div class="mb-3"><label>Confirm Password</label><input name="password_confirmation" type="password" class="form-control"></div>
  <div class="mb-3"><label>Role</label><select name="role" class="form-control"><option value="user">user</option><option value="superadmin">superadmin</option></select></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
