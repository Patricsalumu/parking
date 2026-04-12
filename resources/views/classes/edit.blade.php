@extends('layouts.app')

@section('content')
<h3>Edit Classe</h3>
<form method="POST" action="{{ route('classes.update', $classe) }}">
  @csrf @method('PUT')
  <div class="mb-3"><label>Nom</label><input name="nom" value="{{ $classe->nom }}" class="form-control"></div>
  <div class="mb-3"><label>Numero</label><input name="numero" value="{{ $classe->numero }}" class="form-control"></div>
  <div class="mb-3"><label>Type</label><input name="type" value="{{ $classe->type }}" class="form-control"></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
