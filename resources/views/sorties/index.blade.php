@extends('layouts.app')

@section('content')
<div class="mb-3"><h3>Sorties (Véhicules à l'intérieur)</h3></div>
<div class="d-flex justify-content-between align-items-center mb-3">
  <form method="GET" class="d-flex">
    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm me-2" placeholder="Plaque, client, utilisateur">
    <input type="date" name="start_date" value="{{ $start ?? request('start_date') }}" class="form-control form-control-sm me-2">
    <input type="date" name="end_date" value="{{ $end ?? request('end_date') }}" class="form-control form-control-sm me-2">
    <button class="btn btn-outline-secondary btn-sm">Filtrer</button>
    <a href="{{ route('sorties.index') }}" class="btn btn-light btn-sm ms-2">Clear</a>
  </form>
</div>

<div class="row g-3 mb-3">
  <div class="col-sm-4">
    <div class="card quick-card shadow-sm">
      <div class="card-body">
        <h6 class="card-title">Entrées</h6>
        <h2 class="mb-0">{{ $entriesCount ?? 0 }}</h2>
        <small class="text-muted">Entre {{ $start }} et {{ $end }}</small>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card quick-card shadow-sm">
      <div class="card-body">
        <h6 class="card-title">Sorties</h6>
        <h2 class="mb-0">{{ $sortiesCount ?? 0 }}</h2>
        <small class="text-muted">Entre {{ $start }} et {{ $end }}</small>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card quick-card shadow-sm">
      <div class="card-body">
        <h6 class="card-title">Stock (présents)</h6>
        <h2 class="mb-0">{{ $stockCount ?? 0 }}</h2>
        <small class="text-muted">Présents dans la période</small>
      </div>
    </div>
  </div>
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Numero</th>
      <th>Plaque</th>
      <th>Compagnie</th>
      <th class="d-none d-md-table-cell">Marque</th>
      <th class="d-none d-md-table-cell">Pays</th>
      <th class="d-none d-md-table-cell">Essieux</th>
      <th>Client</th>
      <th>Date Entrée</th>
      <th>Date Sortie</th>
      <th>Utilisateur</th>
      <th>Facturation</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($entrees as $e)
    <tr class="{{ $e->date_sortie ? 'table-danger' : '' }}">
      <td>{{ $entrees->firstItem() + $loop->index }}</td>
      <td>{{ $e->numero_formatted ?? $e->numero }}</td>
      <td>{{ $e->vehicule?->plaque }}</td>
      <td>{{ $e->vehicule?->compagnie ?? '-' }}</td>
      <td class="d-none d-md-table-cell">{{ $e->vehicule?->marque ?? '-' }}</td>
      <td class="d-none d-md-table-cell">{{ $e->vehicule?->pays ?? '-' }}</td>
      <td class="d-none d-md-table-cell">{{ $e->vehicule?->essieux ?? '-' }}</td>
      <td>{{ $e->client?->nom }}</td>
      <td>{{ $e->date_entree ? \Carbon\Carbon::parse($e->date_entree)->format('Y-m-d H:i') : '' }}</td>
      <td>{{ $e->date_sortie ? \Carbon\Carbon::parse($e->date_sortie)->format('Y-m-d H:i') : '-' }}</td>
      <td>{{ $e->user?->name }}</td>
      <td>
        @if($e->facturation)
          #{{ $e->facturation->numero_formatted ?? $e->facturation->numero ?? $e->facturation->id }} - {{ $e->categorie?->nom ?? ($e->facturation->categorie?->nom ?? 'N/C') }} - D: {{ $e->facturation->duree ?? $e->durationInDays() ?? 'N/A' }}
        @else
          <span class="text-muted">Aucune</span>
        @endif
      </td>
      <td>
        <a href="{{ route('sorties.show', $e) }}" class="btn btn-sm btn-outline-secondary me-1">Voir</a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

{{ $entrees->links() }}

<!-- Modal container for view/edit -->
<div class="modal fade" id="sortieModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="sortieModalContent">
      <!-- loaded via AJAX -->
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  try {
    const modalEl = document.getElementById('sortieModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    function loadAndShow(id){
      fetch("/sorties/"+id, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
        .then(r => r.text())
        .then(html => {
          document.getElementById('sortieModalContent').innerHTML = html;
          modal.show();
          // initialize inner confirm modal if present
          const innerConfirm = document.getElementById('confirmSortieModal');
          if(innerConfirm){
            bootstrap.Modal.getOrCreateInstance(innerConfirm);
          }
        }).catch((e)=>{
          console.error('Erreur fetch sorties:', e);
          alert('Erreur réseau');
        });
    }

    // Le bouton 'Sortie' a été supprimé ; ouverture/confirmation se fera depuis "Voir".
    // delegate submit for confirmSortieForm inside loaded modal content
    document.getElementById('sortieModalContent').addEventListener('submit', function(e){
      const form = e.target;
      if (form && form.id === 'confirmSortieForm') {
        e.preventDefault();
        const url = form.action;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(url, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': token || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({})
        }).then(r => r.json()).then(data => {
          if (data && data.success) {
            // close outer modal and inner modal
            const outer = document.getElementById('sortieModal');
            bootstrap.Modal.getInstance(outer)?.hide();
            // refresh page to reflect sortie
            window.location.reload();
          } else {
            alert(data?.message || 'Erreur lors de la sortie');
          }
        }).catch(err => {
          console.error('Sortie confirm error', err);
          alert('Erreur réseau lors de la confirmation');
        });
      }
    });
  } catch(err) {
    console.error('Sorties script error:', err);
  }
});
</script>
@endpush

@endsection
