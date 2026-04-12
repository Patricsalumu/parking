@extends('layouts.app')

@section('content')
<h3>Create Categorie</h3>
<form method="POST" action="{{ route('categories.store') }}">
  @csrf
  <div class="mb-3"><label>Nom</label><input name="nom" class="form-control"></div>
  <div class="mb-3"><label>Prix par 24h</label><input name="prix_par_24h" class="form-control" type="number" step="0.01"></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
