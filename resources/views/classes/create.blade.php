@extends('layouts.app')

@section('content')
<h3>Create Classe</h3>
<form method="POST" action="{{ route('classes.store') }}">
  @csrf
  <div class="mb-3"><label>Nom</label><input name="nom" class="form-control"></div>
  <div class="mb-3"><label>Numero</label><input name="numero" class="form-control"></div>
  <div class="mb-3"><label>Type</label><input name="type" class="form-control"></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
