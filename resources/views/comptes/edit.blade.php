@extends('layouts.app')

@section('content')
<h3>Edit Compte</h3>
<form method="POST" action="{{ route('comptes.update', $compte) }}">
  @csrf @method('PUT')
  <div class="mb-3"><label>Nom</label><input name="nom" value="{{ $compte->nom }}" class="form-control"></div>
  <div class="mb-3"><label>Numero</label><input name="numero" value="{{ $compte->numero }}" class="form-control"></div>
  <div class="mb-3"><label>Classe</label>
    <select name="classe_id" class="form-control">
      @foreach($classes as $cl)
        <option value="{{ $cl->id }}" {{ $compte->classe_id == $cl->id ? 'selected' : '' }}>{{ $cl->nom }}</option>
      @endforeach
    </select>
  </div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
