@extends('layouts.app')

@section('content')
<h3>New Entrée</h3>
<form method="POST" action="{{ route('entrees.store') }}">
  @csrf
  <div class="mb-3">
    <label>Client (select or create inline)</label>
    <select name="client_id" class="form-control">
      <option value="">-- select --</option>
      @foreach($clients as $c)
        <option value="{{ $c->id }}">{{ $c->nom }}</option>
      @endforeach
    </select>
    <small class="text-muted">Or create: <input name="client_nom" class="form-control mt-1" placeholder="Client name"></small>
  </div>

  <div class="mb-3">
    <label>Vehicule (select or plaque)</label>
    <select name="vehicule_id" class="form-control">
      <option value="">-- select --</option>
      @foreach($vehicules as $v)
        <option value="{{ $v->id }}">{{ $v->plaque }}</option>
      @endforeach
    </select>
    <small class="text-muted">Or plaque: <input name="plaque" class="form-control mt-1" placeholder="Plate number"></small>
  </div>

  <div class="mb-3"><label>Observation</label><textarea name="observation" class="form-control"></textarea></div>
  <div class="mb-3"><label>QR Code (optional)</label><input name="qr_code" class="form-control"></div>
  <button class="btn btn-success">Save Entry</button>
</form>
@endsection
