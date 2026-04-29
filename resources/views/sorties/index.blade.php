@extends('layouts.app')

@section('content')
<div class="mb-3"><h3>Sorties (Véhicules à l'intérieur)</h3></div>
<div class="mb-3">
  <form method="GET" class="row g-2 align-items-center">
    <div class="col-12 col-md-4">
      <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Plaque, client, utilisateur">
    </div>
    <div class="col-6 col-md-3">
      <input type="date" name="start_date" value="{{ $start ?? request('start_date') }}" class="form-control form-control-sm">
    </div>
    <div class="col-6 col-md-3">
      <input type="date" name="end_date" value="{{ $end ?? request('end_date') }}" class="form-control form-control-sm">
    </div>
    <div class="col-12 col-md-2 d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" aria-label="Filtrer"><i class="bi bi-funnel" aria-hidden="true"></i></button>
      <a href="{{ route('sorties.index') }}" class="btn btn-light btn-sm">Clear</a>
    </div>
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
      <th>Depuis facturation</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($entrees as $e)
    <tr id="entree-row-{{ $e->id }}" data-id="{{ $e->id }}">
      <td>{{ $entrees->firstItem() + $loop->index }}</td>
      <td class="entree-plaque">{{ $e->vehicule?->plaque }}</td>
      <td>{{ $e->vehicule?->compagnie ?? '-' }}</td>
      <td class="d-none d-md-table-cell">{{ $e->vehicule?->marque ?? '-' }}</td>
      <td class="d-none d-md-table-cell">{{ $e->vehicule?->pays ?? '-' }}</td>
      <td class="d-none d-md-table-cell">{{ $e->vehicule?->essieux ?? '-' }}</td>
      <td>{{ $e->client?->nom }}</td>
      <td>{{ $e->date_entree ? \Carbon\Carbon::parse($e->date_entree)->format('Y-m-d H:i') : '' }}</td>
      <td class="entree-date-sortie">{{ $e->date_sortie ? \Carbon\Carbon::parse($e->date_sortie)->format('Y-m-d H:i') : '-' }}</td>
      <td>{{ $e->user?->name }}</td>
      <td>
        @if($e->facturation)
          #{{ $e->facturation->id }} - {{ $e->categorie?->nom ?? ($e->facturation->categorie?->nom ?? 'N/C') }} - D: {{ $e->facturation->duree ?? $e->durationInDays() ?? 'N/A' }}
        @else
          <span class="text-muted">Aucune</span>
        @endif
      </td>
      <td class="text-nowrap entree-since-billed {{ $e->date_sortie ? 'text-danger' : '' }}">
        @if($e->sinceBilled)
          {{ $e->sinceBilled['days'] }}j {{ $e->sinceBilled['hours'] }}h {{ $e->sinceBilled['minutes'] }}m
        @else
          -
        @endif
      </td>
      <td class="entree-actions">
        <a href="{{ route('sorties.show', $e) }}" class="btn btn-sm btn-outline-secondary me-1">Voir</a>
        @php
          $fact = $e->facturation;
          $canExit = false;
          if ($fact) {
            $canExit = (($fact->montant_paye ?? 0) >= ($fact->montant_total ?? 0));
          }
        @endphp
        @if($e->date_sortie)
          <button class="btn btn-sm btn-light" disabled>Déjà sorti</button>
        @else
          <button class="btn btn-sm {{ $canExit ? 'btn-danger btn-exit' : 'btn-secondary' }}" data-id="{{ $e->id }}" {{ $canExit ? '' : 'disabled' }}>Sortie</button>
        @endif
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

<!-- Toast container for notifications -->
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index:1080"></div>

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

    document.querySelectorAll('.btn-exit').forEach(btn => {
      btn.addEventListener('click', function(){ loadAndShow(this.dataset.id); });
    });
    // delegate submit for confirmSortieForm inside loaded modal content
    document.getElementById('sortieModalContent').addEventListener('submit', function(e){
      const form = e.target;
        if (form && form.id === 'confirmSortieForm') {
        e.preventDefault();
        const url = form.action;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.debug('confirmSortieForm submit', url);
        fetch(url, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': token || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({})
        }).then(r => {
          console.debug('confirmSortieForm response status', r.status, r.headers.get('content-type'));
          return r.json();
        }).then(data => {
          console.debug('confirmSortieForm data', data);
          if (data && data.success) {
            const outer = document.getElementById('sortieModal');
            bootstrap.Modal.getInstance(outer)?.hide();
            window.location.reload();
          } else {
            showToast(data?.message || 'Erreur lors de la sortie', 'danger');
          }
        }).catch(err => {
          console.error('Sortie confirm error', err);
          showToast('Erreur réseau lors de la confirmation', 'danger');
        });
      }
      if (form && form.id === 'confirmApurerForm') {
        e.preventDefault();
        const url = form.action;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.debug('confirmApurerForm submit', url);
        fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': token || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({})
        }).then(r => {
          console.debug('confirmApurerForm response status', r.status, r.headers.get('content-type'));
          return r.json();
        }).then(data => {
          console.debug('confirmApurerForm data', data);
          if (data && data.success) {
            const outer = document.getElementById('sortieModal');
            bootstrap.Modal.getInstance(outer)?.hide();
            showToast(data.message || 'Facture apurée', 'success');
            // update table row in-place
            if (data.entree && data.entree.id) {
              const row = document.getElementById('entree-row-' + data.entree.id);
              if (row) {
                // update date_sortie cell
                const dsCell = row.querySelector('.entree-date-sortie');
                if (dsCell) dsCell.textContent = data.entree.date_sortie || '-';
                // update since billed
                const sbCell = row.querySelector('.entree-since-billed');
                if (sbCell) {
                  sbCell.textContent = data.entree.sinceBilled || '-';
                  sbCell.classList.add('text-danger');
                }
                // mark plaque as muted/struck
                const plaqueCell = row.querySelector('.entree-plaque');
                if (plaqueCell) plaqueCell.classList.add('text-decoration-line-through','text-muted');
                // replace actions with disabled "Déjà sorti"
                const actionsCell = row.querySelector('.entree-actions');
                if (actionsCell) {
                  actionsCell.innerHTML = '<button class="btn btn-sm btn-light" disabled>Déjà sorti</button>';
                }
              }
            }
          } else {
            showToast(data?.message || 'Erreur lors de l\'apurement', 'danger');
          }
        }).catch(err => {
          console.error('Apurer error', err);
          showToast('Erreur réseau lors de l\'apurement', 'danger');
        });
      }
    });
  } catch(err) {
    console.error('Sorties script error:', err);
  }
});

    function showToast(message, type = 'info'){
      try {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const bgClass = type === 'success' ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-secondary text-white');
        const toastId = 'toast-'+Date.now();
        const toastHtml = `
          <div id="${toastId}" class="toast ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="1200">
            <div class="d-flex">
              <div class="toast-body">${message}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>`;
        container.insertAdjacentHTML('beforeend', toastHtml);
        const el = document.getElementById(toastId);
        const bsToast = new bootstrap.Toast(el);
        el.addEventListener('hidden.bs.toast', () => { el.remove(); });
        bsToast.show();
      } catch(e) { console.error('showToast error', e); }
    }
</script>
@endpush

@endsection
