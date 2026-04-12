@extends('layouts.app')

@section('content')
<h3>Create Vehicule</h3>
<form method="POST" action="{{ route('vehicules.store') }}">
  @csrf
  <div class="mb-3"><label>Plaque</label><input name="plaque" class="form-control"></div>
  <div class="mb-3"><label>Marque</label><input name="marque" class="form-control"></div>
  <div class="mb-3"><label>Client</label>
    <select name="client_id" class="form-control">
      <option value="">-- none --</option>
      @foreach($clients as $c)
        <option value="{{ $c->id }}">{{ $c->nom }}</option>
      @endforeach
    </select>
  </div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
