@extends('layouts.app')

@section('content')
<h3>Edit Entrée</h3>
<form method="POST" action="{{ route('entrees.update', $entree) }}">
  @csrf @method('PUT')
  <div class="mb-3"><label>Date Sortie</label><input name="date_sortie" type="datetime-local" value="{{ optional($entree->date_sortie)->format('Y-m-d\TH:i') }}" class="form-control"></div>
  <div class="mb-3"><label>Observation</label><textarea name="observation" class="form-control">{{ $entree->observation }}</textarea></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
