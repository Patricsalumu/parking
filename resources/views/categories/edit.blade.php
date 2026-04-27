@extends('layouts.app')

@section('content')
<h3>Edit Categorie</h3>
<form method="POST" action="{{ route('categories.update', $category) }}">
  @csrf @method('PUT')
  <div class="mb-3"><label>Nom</label><input name="nom" value="{{ $category->nom }}" class="form-control"></div>
  <div class="mb-3"><label>Prix par 24h</label><input name="prix_par_24h" value="{{ $category->prix_par_24h }}" class="form-control" type="number" step="0.01"></div>
  <div class="mb-3">
    <label>Compte produit</label>
    <select name="compte_produit_id" class="form-control" required>
      <option value="">-- Choisir un compte produit --</option>
      @foreach($comptes as $compte)
        <option value="{{ $compte->id }}" {{ $category->compte_produit_id == $compte->id ? 'selected' : '' }}>{{ $compte->numero }} - {{ $compte->nom }}</option>
      @endforeach
    </select>
  </div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
