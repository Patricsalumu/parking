@extends('layouts.app')

@section('content')
<h3>Facturation #{{ $facturation->id }}</h3>
<div class="card p-3">
  <p><strong>Entrée:</strong> {{ $facturation->entree_id }}</p>
  <p><strong>Vehicule:</strong> {{ $facturation->entree->vehicule?->plaque }}</p>
  <p><strong>Durée (jours):</strong> {{ $facturation->duree }}</p>
  <p><strong>Total:</strong> {{ $facturation->montant_total }}</p>
  <p><strong>Montant payé:</strong> {{ $facturation->montant_paye }}</p>
  <p><strong>Balance:</strong> {{ $facturation->montant_total - $facturation->montant_paye }}</p>
</div>
@endsection
