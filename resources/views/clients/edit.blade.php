@extends('layouts.app')

@section('content')
<h3>Edit Client</h3>
<form method="POST" action="{{ route('clients.update', $client) }}">
  @csrf @method('PUT')
  <div class="mb-3"><label>Nom</label><input name="nom" value="{{ $client->nom }}" class="form-control"></div>
  <div class="mb-3"><label>Email</label><input name="email" value="{{ $client->email }}" class="form-control"></div>
  <div class="mb-3"><label>Telephone</label><input name="telephone" value="{{ $client->telephone }}" class="form-control"></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
