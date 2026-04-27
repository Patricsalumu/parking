@extends('layouts.app')

@section('content')
<div style="max-width:420px;margin:0 auto;font-family:monospace;">
  <h4 style="text-align:center">{{ $entreprise->nom ?? 'Entreprise' }}</h4>
  <p style="text-align:center">{{ $entreprise->slogan ?? '' }}</p>

  <div style="text-align:left;margin-bottom:6px;">
    <a href="{{ route('facturations.index') }}" class="btn btn-secondary btn-sm">← Retour</a>
  </div>
  <div style="text-align:center;margin-top:8px;">
    <div style="font-weight:700">Facture N° {{ $facturation->numero_formatted ?? $facturation->numero ?? $facturation->id }}</div>
    <div style="font-size:0.95em">{{ $facturation->created_at ? \Carbon\Carbon::parse($facturation->created_at)->format('Y-m-d H:i') : '' }}</div>
  </div>

  <hr>

  <h5 style="text-align:left;margin-top:6px;">Informations véhicules</h5>
  <div style="margin-left:6px;">
    <p><strong>Numéro entrée :</strong> {{ $facturation->entree_id }}</p>
    <p><strong>Plaque :</strong> {{ $facturation->entree->vehicule?->plaque ?? '-' }}</p>
    <p><strong>Client :</strong> {{ $facturation->entree->client?->nom ?? '-' }}</p>
    <p><strong>Compagnie :</strong> {{ $facturation->entree->vehicule?->compagnie ?? '-' }}</p>
    <p><strong>Date entrée :</strong> {{ $facturation->entree->date_entree ? \Carbon\Carbon::parse($facturation->entree->date_entree)->format('Y-m-d H:i') : '-' }}</p>
  </div>

  <hr>

  @php
    // Read values directly from DB as requested
    $catId = $facturation->entree->categorie?->id ?? $facturation->categorie?->id ?? $facturation->categorie_id ?? null;
    $days = $facturation->duree ?? 0;
    $hours = 0;
    $minutes = 0;
    $price = $facturation->entree->categorie?->prix_par_24h ?? $facturation->categorie?->prix_par_24h ?? $facturation->prix_unitaire ?? null;
    $displaySubtotal = $facturation->montant_total ?? 0;
    $displayTotal = $facturation->montant_total ?? 0;
    $paid = $facturation->montant_paye ?? 0;
    $reduction = $facturation->reduction ?? 0;
    $balance = $displayTotal - $paid;
    $taux = $entreprise->taux_change ?? $facturation->taux_change ?? config('app.taux_change') ?? 600;
    $totalUsd = $taux ? round($displayTotal / $taux,2) : 0;
    $showCat1Note = ($catId == 1 && ($facturation->montant_total ?? 0) == 0);
  @endphp

  <h5 style="margin-top:6px;">Détails facturation</h5>
  <div style="margin-left:6px;">
    <p><strong>Catégorie :</strong> {{ $facturation->entree->categorie?->nom ?? $facturation->categorie?->nom ?? '-' }}</p>
    <p><strong>Durée :</strong> {{ $days }} jours</p>
    <p><strong>Prix catégorie :</strong> {{ $price ? number_format($price,2) : '-' }}</p>
    <p><strong>Montant total :</strong> {{ number_format($displayTotal,2) }}</p>
    <p><strong>Réduction :</strong> {{ number_format($reduction,2) }}</p>
    <p><strong>Payé :</strong> {{ number_format($paid,2) }}</p>
    <p><strong>Reste :</strong> {{ number_format($balance,2) }}</p>
    @if($showCat1Note)
      <p style="color:#a00"><strong>Non facturé :</strong> pas de nuitée (catégorie 1).</p>
    @endif
    <p><strong>Total (FC) :</strong> {{ number_format($displayTotal,2) }}</p>
    <p><strong>Taux :</strong> {{ $taux }}</p>
    <p><strong>Total (USD) :</strong> {{ number_format($taux ? round($displayTotal / $taux,2) : 0,2) }}</p>
    <p><strong>Payé :</strong> {{ number_format($paid,2) }}</p>
    <p><strong>Reste :</strong> {{ number_format($balance,2) }}</p>
    <p><strong>Caissier :</strong> {{ $facturation->caissier?->name ?? $facturation->user?->name ?? '-' }}</p>
    @php
      $status = 'Non payé';
      if(($facturation->montant_paye ?? 0) >= ($facturation->montant_total ?? 0)) $status = 'Payé';
      elseif(($facturation->montant_paye ?? 0) > 0) $status = 'Partiel';
    @endphp
    <p><strong>Statut :</strong> {{ $status }}</p>
  </div>

  <div style="text-align:center;margin-top:12px">
    @if(class_exists('SimpleSoftwareIO\\QrCode\\Facades\\QrCode') || class_exists('QrCode'))
      {!! QrCode::size(150)->generate(url('/facturations/'.$facturation->id)) !!}
    @endif
  </div>

  <div style="text-align:center;margin-top:8px">Merci</div>

</div>
<script>window.print()</script>
@endsection
