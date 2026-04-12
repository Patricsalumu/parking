@extends('layouts.app')

@section('content')
<a href="{{ route('comptes.index') }}" class="btn btn-light btn-sm mb-3">Retour</a>
<h3>Créer Compte</h3>
<form method="POST" action="{{ route('comptes.store') }}">
  @csrf
  <div class="mb-3"><label>Numéro</label><input name="numero" value="{{ old('numero') }}" class="form-control"></div>
  <div class="mb-3"><label>Intitulé</label><input name="nom" value="{{ old('nom') }}" class="form-control"></div>
  <div class="mb-3"><label>Classe</label>
    <select name="classe_id" class="form-control">
      @foreach($classes as $cl)
        <option value="{{ $cl->id }}">{{ $cl->numero ?? $cl->id }} - {{ $cl->nom }}</option>
      @endforeach
    </select>
  </div>
  <button class="btn btn-success">Enregistrer</button>
</form>
@endsection
@extends('layouts.app')

@section('content')
<h3>Create Compte</h3>
<form method="POST" action="{{ route('comptes.store') }}">
  @csrf
  <div class="mb-3"><label>Nom</label><input name="nom" class="form-control"></div>
  <div class="mb-3"><label>Numero</label><input name="numero" class="form-control"></div>
  <div class="mb-3"><label>Classe</label>
    <select name="classe_id" class="form-control">
      @foreach($classes as $cl)
        <option value="{{ $cl->id }}">{{ $cl->nom }}</option>
      @endforeach
    </select>
  </div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
