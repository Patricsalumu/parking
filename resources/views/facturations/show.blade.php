@extends('layouts.app')

@section('content')
<a href="{{ route('facturations.index') }}" class="btn btn-secondary mb-2">← Retour</a>
<h3>Facturation #{{ $facturation->numero_formatted ?? $facturation->numero ?? $facturation->id }}</h3>
<div class="card p-3">
  @php // format_dt helper is used below to show timezone-aware dates with label @endphp
  <p><strong>Entrée:</strong> {{ $facturation->entree_id }}</p>
  <p><strong>Numero Facture:</strong> {{ $facturation->numero_formatted ?? $facturation->numero ?? $facturation->id }}</p>
  <p><strong>Date facturation:</strong> {{ $facturation->updated_at ? format_dt($facturation->updated_at) : ($facturation->created_at ? format_dt($facturation->created_at) : '—') }}</p>
  <p><strong>Véhicule - Plaque:</strong> {{ $facturation->entree->vehicule?->plaque }}</p>
  <p><strong>Marque:</strong> {{ $facturation->entree->vehicule?->marque }}</p>
  <p><strong>Pays:</strong> {{ $facturation->entree->vehicule?->pays }}</p>
  <p><strong>Essieux:</strong> {{ $facturation->entree->vehicule?->essieux }}</p>
  <p><strong>Client:</strong> {{ $facturation->entree->client?->nom }}</p>
  <p><strong>Date entrée:</strong> {{ $facturation->entree->date_entree ? format_dt($facturation->entree->date_entree) : '—' }}</p>
  <p><strong>Date sortie:</strong> {{ $facturation->entree->date_sortie ? format_dt($facturation->entree->date_sortie) : '—' }}</p>
  <p><strong>Durée (jours enregistrés):</strong> {{ $facturation->duree }}</p>
  @php
    $start = $facturation->entree->date_entree ? \Carbon\Carbon::parse($facturation->entree->date_entree) : null;
    $end = $facturation->entree->date_sortie ? \Carbon\Carbon::parse($facturation->entree->date_sortie) : null;
    $end = $end ?? \Carbon\Carbon::now();
    if ($start) {
      $diffMinutes = $end->diffInMinutes($start);
      $hours = intdiv($diffMinutes, 60);
      $minutes = $diffMinutes % 60;
      $daysCeil = max(1, (int) ceil($hours / 24));
      $remainder = ($hours > 24) ? (($hours - 24) % 24) : 0;
    } else {
      $hours = $minutes = $diffMinutes = 0;
      $daysCeil = $facturation->duree ?? 0;
      $remainder = 0;
    }
    $categorie = $facturation->entree->categorie_id ? \App\Models\Categorie::find($facturation->entree->categorie_id) : null;
    $catId = $categorie?->id ?? null;
  @endphp
  <p><strong>Durée détaillée:</strong> {{ $hours }}h {{ $minutes }}m</p>
  <p><strong>Jours calculés:</strong> {{ $daysCeil }}</p>
  <p><strong>Catégorie (entrée):</strong> {{ $categorie?->nom ?? '—' }}</p>
  <p><strong>Montant total:</strong> {{ number_format($facturation->montant_total,2) }}</p>
  <p><strong>Réduction:</strong> {{ number_format($facturation->reduction ?? 0,2) }}</p>
  <p><strong>Net à payer:</strong> {{ number_format( ($facturation->montant_total - ($facturation->reduction ?? 0)),2) }}</p>
  <p><strong>Montant payé:</strong> {{ number_format($facturation->montant_paye ?? 0,2) }}</p>
  <p><strong>Reste:</strong> {{ number_format( ($facturation->montant_total - ($facturation->montant_paye ?? 0)),2) }}</p>
  <p><strong>Facturé par:</strong> {{ $facturation->user?->name ?? '—' }}</p>
  @if($facturation->paiements && $facturation->paiements->count() > 0)
    <p><strong>Paiements enregistrés:</strong></p>
    <ul>
      @foreach($facturation->paiements as $p)
        <li>
          {{ $p->date_paiement ? format_dt($p->date_paiement) : ($p->created_at ? format_dt($p->created_at) : '') }} — {{ number_format($p->montant,2) }}
          ({{ $p->mode ?? '—' }})
          @if($p->user)
            — Reçu par: {{ $p->user->name }}
          @endif
          @if($p->note) — {{ $p->note }} @endif
        </li>
      @endforeach
    </ul>
  @else
    <p><strong>Paiements enregistrés:</strong> Aucun</p>
  @endif
  @php
    // alerts for special cases
    $alert = null;
    if ($catId == 1 && $start && $start->toDateString() === \Carbon\Carbon::now()->toDateString()) {
      $alert = '<div class="alert alert-warning">Catégorie 1 : pas de nuitée détectée → facturation = 0.</div>';
    } elseif ($catId == 2 && isset($remainder) && ($remainder = (int) (($hours - 24) % 24)) && ($hours > 24) && $remainder > 0 && $remainder <= 5) {
      $alert = '<div class="alert alert-info">Catégorie 2 : dernier jour partiel ≤ 5h → dernier jour facturé à 50%.</div>';
    }
  @endphp
  {!! $alert ?? '' !!}
</div>
<div class="mt-2 d-flex gap-2">
  <button id="btnPayShow" class="btn btn-success">Payer</button>
  <a href="{{ route('facturations.print', $facturation) }}" target="_blank" class="btn btn-primary">Imprimer</a>
</div>

<!-- Payment Modal (show) -->
<div class="modal fade" id="payModalShow" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="payFormShow" method="POST" action="{{ route('paiements.store') }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Enregistrer Paiement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="facturation_id" id="modal_show_facturation_id" value="{{ $facturation->id }}">
          <div class="mb-3"><label>Montant</label><input id="modal_show_montant" name="montant" class="form-control" required></div>
          <div class="mb-3">
            <label>Mode</label>
            <select name="mode" class="form-select">
              <option value="espece">Espèce</option>
              <option value="mobile">Mobile</option>
              <option value="banque">Banque</option>
            </select>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-success">Enregistrer</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button></div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const payModal = new bootstrap.Modal(document.getElementById('payModalShow'));
  const btn = document.getElementById('btnPayShow');
  btn.addEventListener('click', function(){
    const balance = parseFloat('{{ $facturation->montant_total - $facturation->montant_paye }}') || 0;
    document.getElementById('modal_show_montant').value = balance.toFixed(2);
    payModal.show();
  });
});
</script>
@endpush
@endsection
