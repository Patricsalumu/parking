@extends('layouts.app')

@section('content')
<div style="max-width:420px;margin:0 auto;font-family:monospace;">
  <h4 style="text-align:center">{{ $entreprise->nom ?? 'Entreprise' }}</h4>
  <p style="text-align:center">{{ $entreprise->slogan ?? '' }}</p>
  <hr>
  <h5>Facture #{{ $facturation->id }}</h5>
  <p><strong>Date:</strong> {{ $facturation->created_at->format('Y-m-d H:i') }}</p>
  <p><strong>Entrée:</strong> {{ $facturation->entree_id }}</p>
  <p><strong>Plaque:</strong> {{ $facturation->entree->vehicule?->plaque }}</p>
  <p><strong>Client:</strong> {{ $facturation->entree->client?->nom }}</p>
  <p><strong>Catégorie:</strong> {{ $facturation->categorie?->nom }}</p>
  <p><strong>Durée (jours):</strong> {{ $facturation->duree }}</p>
  <p><strong>Total:</strong> {{ number_format($facturation->montant_total,2) }}</p>
  <p><strong>Réduction:</strong> {{ number_format($facturation->reduction,2) }}</p>
  <p><strong>Payé:</strong> {{ number_format($facturation->montant_paye,2) }}</p>
  <p><strong>Balance:</strong> {{ number_format($facturation->montant_total - $facturation->montant_paye,2) }}</p>
  <div style="text-align:center;margin-top:12px">
    @if(class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode') || class_exists('QrCode'))
      {!! QrCode::size(150)->generate(url('/facturations/'.$facturation->id)) !!}
    @endif
  </div>
  <div style="text-align:center;margin-top:8px">Merci</div>
</div>
<script>window.print()</script>
@endsection
