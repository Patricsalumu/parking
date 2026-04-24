@extends('layouts.app')

@section('content')
<div class="mb-3">
  <h3>Facturations</h3>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex">
    <input id="indexSearchPlaque" class="form-control form-control-sm me-2" placeholder="Rechercher plaque et créer facture">
    <button id="indexBtnSearch" class="btn btn-primary btn-sm me-3">Chercher & Créer</button>
    <form method="GET" class="d-flex">
      <input type="date" name="start_date" value="{{ $start ?? request('start_date') }}" class="form-control form-control-sm me-2">
      <input type="date" name="end_date" value="{{ $end ?? request('end_date') }}" class="form-control form-control-sm me-2">
      <button class="btn btn-outline-secondary btn-sm me-2">Filtrer</button>
      <a href="{{ route('facturations.index') }}" class="btn btn-light btn-sm">Clear</a>
    </form>
  </div>
  @php $qs = http_build_query(request()->except('page')) @endphp
  <div class="ms-3">
    <a href="{{ route('facturations.export.csv') }}?{{ $qs }}" class="btn btn-outline-success btn-sm">Export CSV</a>
    <a href="{{ route('facturations.export.pdf') }}?{{ $qs }}" class="btn btn-outline-primary btn-sm">Export PDF</a>
  </div>
</div>
<div class="mb-3">
  <div class="d-flex gap-3">
    <div>Total facturé: <span class="badge bg-secondary">{{ number_format($totalBilled ?? 0,2) }}</span></div>
    <div>Total payé: <span class="badge bg-success">{{ number_format($totalPaid ?? 0,2) }}</span></div>
    <div>Non payés: <span class="badge bg-warning text-dark">{{ number_format($totalRemaining ?? 0,2) }}</span></div>
  </div>
</div>
<table class="table table-striped">
  <thead>
    <tr>
    <th>#</th>
      <th>Entrée</th>
      <th>Vehicule</th>
      <th>Catégorie</th>
      <th>Durée</th>
      <th>Utilisateur</th>
      <th>Total</th>
      <th>Payé</th>
      <th>Reste</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($facturations as $f)
      <tr>
        <td>{{ $facturations->firstItem() + $loop->index }}</td>
        <td>{{ $f->entree_id }}</td>
        <td>{{ $f->entree->vehicule?->plaque }}</td>
        <td>{{ $f->entree?->categorie?->nom ?? ($f->categorie?->nom ?? 'N/C') }}</td>
            @php
              $duration = 'N/A';
              if ($f->entree && $f->entree->date_entree) {
                $start = \Carbon\Carbon::parse($f->entree->date_entree);
                $end = $f->entree->date_sortie ? \Carbon\Carbon::parse($f->entree->date_sortie) : \Carbon\Carbon::now();
                $days = $end->diffInDays($start);
                $hours = $end->diffInHours($start) % 24;
                $minutes = $end->diffInMinutes($start) % 60;
                $duration = $days . 'j ' . $hours . 'h ' . $minutes . 'm';
              }
            @endphp
            <td>@if($f->entree?->date_sortie)<span class="text-danger">{{ $duration }}</span>@else{{ $duration }}@endif</td>
            <td>{{ $f->user?->name ?? $f->entree->user?->name }}</td>
        <td>{{ number_format($f->montant_total,2) }}</td>
        <td>{{ number_format($f->montant_paye ?? 0,2) }}</td>
        <td>{{ number_format(($f->montant_total - ($f->montant_paye ?? 0)),2) }}</td>
        <td>
          <button class="btn btn-sm btn-success btn-pay" data-id="{{ $f->id }}" data-balance="{{ $f->montant_total - $f->montant_paye }}">Payer</button>
          <a href="{{ route('facturations.print', $f) }}" target="_blank" class="btn btn-sm btn-primary">Imprimer</a>
          <a href="{{ route('facturations.show', $f) }}" class="btn btn-sm btn-outline-secondary">View</a>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
<!-- Payment Modal -->
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="payForm" method="POST" action="{{ route('paiements.store') }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Enregistrer Paiement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="facturation_id" id="modal_facturation_id">
          <div class="mb-3"><label>Montant</label><input id="modal_montant" name="montant" class="form-control" required></div>
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
  const payModal = new bootstrap.Modal(document.getElementById('payModal'));
  document.querySelectorAll('.btn-pay').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      const balance = parseFloat(this.dataset.balance) || 0;
      document.getElementById('modal_facturation_id').value = id;
      document.getElementById('modal_montant').value = balance.toFixed(2);
      payModal.show();
    });
  });

  // Plaque suggestions: dynamic datalist population
  const plaqueInput = document.getElementById('indexSearchPlaque');
  if (plaqueInput) {
    // create datalist element
    let dl = document.getElementById('plaques_list');
    if (!dl) {
      dl = document.createElement('datalist');
      dl.id = 'plaques_list';
      document.body.appendChild(dl);
    }
    plaqueInput.setAttribute('list','plaques_list');

    let debounceTimer = null;
    plaqueInput.addEventListener('input', function(){
      const q = this.value.trim();
      if (debounceTimer) clearTimeout(debounceTimer);
      if (!q) { dl.innerHTML = ''; return; }
      debounceTimer = setTimeout(()=>{
        fetch("{{ route('vehicules.searchPlaques') }}?q="+encodeURIComponent(q), {credentials:'same-origin'})
          .then(r=> r.ok ? r.json() : Promise.reject())
          .then(data=>{
            dl.innerHTML = '';
            window._plaqueSuggestions = window._plaqueSuggestions || {};
            window._plaqueSuggestions = {}; // reset
            (data.results || []).forEach(item => {
              const opt = document.createElement('option');
              opt.value = item.plaque;
              // show plaque + client + compagnie + pays
              const parts = [item.plaque];
              if (item.client) parts.push(item.client);
              if (item.compagnie) parts.push(item.compagnie);
              if (item.pays) parts.push(item.pays);
              opt.textContent = parts.join(' — ');
              dl.appendChild(opt);
              window._plaqueSuggestions[item.plaque] = item;
            });
          }).catch(()=>{ dl.innerHTML = ''; window._plaqueSuggestions = {}; });
      }, 250);
    });
    // when user selects a suggestion (datalist select), the input's 'change' event fires
    plaqueInput.addEventListener('change', function(){
      const v = this.value.trim();
      if (!v) return;
      const item = (window._plaqueSuggestions || {})[v];
      if (item) {
        // prefer redirect with plaque; optionally could include entree_id
        const url = new URL("{{ route('facturations.create') }}", window.location.origin);
        url.searchParams.set('plaque', v);
        // if an open entree exists pass entree_id to help prefilling
        if (item.entree_id) url.searchParams.set('entree_id', item.entree_id);
        window.location.href = url.toString();
      }
    });
  }

});
</script>
@endpush

{{ $facturations->links() }}
@endsection

@push('scripts')
<script>
function showNotFoundModal(message){
  const el = document.getElementById('notFoundModalBody');
  if(el) el.textContent = message || 'Plaque non trouvée';
  const m = new bootstrap.Modal(document.getElementById('notFoundModal'));
  m.show();
}
document.addEventListener('DOMContentLoaded', function(){
  document.getElementById('indexBtnSearch').addEventListener('click', function(e){
    e.preventDefault();
    const plaque = document.getElementById('indexSearchPlaque').value.trim();
    if (!plaque) return showNotFoundModal('Veuillez entrer une plaque');
    fetch("{{ route('facturations.findByPlaque') }}?plaque=" + encodeURIComponent(plaque))
      .then(r => r.json())
      .then(data => {
        if (!data.found) {
          return showNotFoundModal('Plaque non trouvée');
        }
        // redirect to create page with plaque prefilled
        window.location.href = "{{ route('facturations.create') }}?plaque=" + encodeURIComponent(plaque);
      })
      .catch(()=> showNotFoundModal('Erreur réseau'));
  });
});
</script>
@endpush

<!-- Not Found Modal -->
<div class="modal fade" id="notFoundModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Information</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="notFoundModalBody">Plaque non trouvée</div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button></div>
    </div>
  </div>
</div>
