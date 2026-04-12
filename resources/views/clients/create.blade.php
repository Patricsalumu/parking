@extends('layouts.app')

@section('content')
<h3>Create Client</h3>
<form method="POST" action="{{ route('clients.store') }}">
  @csrf
  <div class="mb-3"><label>Nom</label><input name="nom" class="form-control"></div>
  <div class="mb-3"><label>Email</label><input name="email" class="form-control"></div>
  <div class="mb-3"><label>Telephone</label><input name="telephone" class="form-control"></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
