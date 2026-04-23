@extends('layouts.app')

@section('content')
<style>
  @page { size: 80mm auto; margin: 0; }
  @media print {
    html, body { width: 80mm; }
    body { margin: 0; }
    .receipt-container { width: 80mm; max-width: 80mm; margin: 0; }
  }
  .receipt-container { width: 80mm; max-width: 80mm; margin: 0 auto; font-family: monospace; font-size:12px; }
  .small-qr { display:block; margin:8px auto; }
</style>
<div class="receipt-container">
  <h4 style="text-align:center">{{ $entreprise->nom ?? 'Entreprise' }}</h4>
  <p style="text-align:center">{{ $entreprise->slogan ?? '' }}</p>

  <div style="text-align:center;margin-top:8px;">
    <div style="font-weight:700">Facture N° {{ $facturation->numero_formatted ?? $facturation->numero ?? $facturation->id }}</div>
    <div style="font-size:0.95em">{{ $facturation->created_at ? format_dt($facturation->created_at) : '' }}</div>
  </div>

  <hr>

  <h5 style="text-align:left;margin-top:6px;">Informations véhicules</h5>
  <div style="margin-left:6px;">
    <div style="display:flex;justify-content:space-between;gap:6px;">
      <div style="width:48%;"><strong>Numéro entrée :</strong> {{ sprintf('%06d', $facturation->entree_id ?? 0) }}</div>
      <div style="width:48%;text-align:right;"><strong>Date entrée :</strong> {{ $facturation->entree->date_entree ? format_dt($facturation->entree->date_entree) : '-' }}</div>
    </div>
    <div style="display:flex;justify-content:space-between;gap:6px;margin-top:4px;">
      <div style="width:48%;"><strong>Plaque :</strong> {{ $facturation->entree->vehicule?->plaque ?? '-' }}</div>
      <div style="width:48%;text-align:right;"><strong>Compagnie :</strong> {{ $facturation->entree->vehicule?->compagnie ?? '-' }}</div>
    </div>
    <div style="margin-top:4px;"><strong>Client :</strong> {{ $facturation->entree->client?->nom ?? '-' }}</div>
  </div>

  <hr>

  @php
    // Read values directly from DB as requested
    $catId = $facturation->entree->categorie?->id ?? $facturation->categorie?->id ?? $facturation->categorie_id ?? null;
    $days = $facturation->duree ?? 0;
    $hours = 0;
    $minutes = 0;
    $price = $facturation->entree->categorie?->prix_par_24h ?? $facturation->categorie?->prix_par_24h ?? $facturation->prix_unitaire ?? 0;
    // Compute total as days x price (as requested)
    $computedTotal = ($price ?? 0) * ($days ?? 0);
    $displaySubtotal = $computedTotal;
    $displayTotal = $computedTotal;
    $paid = $facturation->montant_paye ?? 0;
    $reduction = $facturation->reduction ?? 0;
    $balance = $displayTotal - $paid;
    $taux = $entreprise->taux_change ?? $facturation->taux_change ?? config('app.taux_change') ?? 600;
    $totalUsd = $taux ? round($displayTotal / $taux,2) : 0;
    $showCat1Note = ($catId == 1 && $displayTotal == 0);
  @endphp

  <h5 style="margin-top:6px;">Détails facturation</h5>
  <div style="margin-left:6px;">
    <div style="display:flex;justify-content:space-between;gap:6px;align-items:center;">
      <div style="width:60%;"><strong>Catégorie :</strong> {{ $facturation->entree->categorie?->nom ?? $facturation->categorie?->nom ?? '-' }}</div>
      <div style="width:38%;text-align:right;"><strong>Prix :</strong> {{ $price ? number_format($price,2) : '-' }}</div>
    </div>

    <div style="display:flex;justify-content:space-between;gap:6px;align-items:center;margin-top:4px;">
      <div style="width:100%;"><strong>Durée :</strong> {{ $days }} jours</div>
    </div>

    @if($showCat1Note)
      <p style="color:#a00;margin-top:4px;"><strong>Non facturé :</strong> pas de nuitée (catégorie 1).</p>
    @endif

    <div style="margin-top:6px;">
      <div style="display:flex;justify-content:space-between;gap:6px;align-items:center;">
        <div style="width:100%;"><strong>Montant total (FC) :</strong> {{ number_format($computedTotal,2) }}</div>
      </div>

      <div style="display:flex;justify-content:flex-start;gap:6px;align-items:center;margin-top:6px;">
        <div><strong>Taux :</strong> 1$ : {{ $taux }} fc</div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:6px;align-items:center;margin-top:6px;">
        <div style="text-align:right;"><strong>Total (USD) :</strong> {{ number_format($taux ? round($computedTotal / $taux,2) : 0,2) }}</div>
      </div>
    </div>

    <div style="display:flex;justify-content:space-between;gap:6px;align-items:center;margin-top:6px;">
      <div style="width:60%;"><strong>Caissier :</strong> {{ $facturation->caissier?->name ?? $facturation->user?->name ?? '-' }}</div>
      @php
        $status = 'Non payé';
        if(($facturation->montant_paye ?? 0) >= ($computedTotal ?? 0)) $status = 'Payé';
        elseif(($facturation->montant_paye ?? 0) > 0) $status = 'Partiel';
      @endphp
      <div style="width:38%;text-align:right;"><strong>Statut :</strong> {{ $status }}</div>
    </div>
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
