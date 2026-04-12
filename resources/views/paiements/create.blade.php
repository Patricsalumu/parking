@extends('layouts.app')

@section('content')
<h3>Register Payment for Facturation #{{ $facturation->id }}</h3>
<form method="POST" action="{{ route('paiements.store') }}">
  @csrf
  <input type="hidden" name="facturation_id" value="{{ $facturation->id }}">
  <div class="mb-3"><label>Montant</label><input name="montant" class="form-control"></div>
  <div class="mb-3"><label>Date</label><input name="date_paiement" type="date" class="form-control"></div>
  <div class="mb-3"><label>Mode</label><input name="mode" class="form-control"></div>
  <button class="btn btn-success">Save Payment</button>
</form>
@endsection
