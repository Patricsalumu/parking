@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center mb-2">
  <a href="{{ route('facturations.index') }}" class="btn btn-sm btn-outline-secondary me-2" title="Retour"><i class="bi bi-arrow-left"></i></a>
  <h3 class="mb-0">Créer Facture depuis Entrée</h3>
</div>

<div class="mb-3">
  <label class="form-label">Recherche par plaque</label>
  <div class="input-group">
    <input id="searchPlaque" type="text" class="form-control" placeholder="Enter plaque">
    <button id="btnSearch" class="btn btn-outline-secondary">Chercher</button>
  </div>
</div>

<div id="resultArea" style="display:none">
  <h5>Entrée trouvée <button id="toggleInfoIcon" class="btn btn-sm btn-outline-secondary ms-2" title="Afficher/Masquer infos"><i class="bi bi-eye-slash"></i></button></h5>
  <div class="card p-3 mb-3">
    <p class="entree-field"><strong>Plaque:</strong> <span id="r_plaque"></span></p>
    <p class="entree-field"><strong>Compagnie:</strong> <span id="r_compagnie"></span></p>
    <p class="entree-field"><strong>Client:</strong> <span id="r_client"></span></p>
    <p class="entree-field"><strong>Date entrée:</strong> <span id="r_date_entree"></span></p>
    <p class="entree-field"><strong>Marque:</strong> <span id="r_marque"></span></p>
    <p class="entree-field"><strong>Pays:</strong> <span id="r_pays"></span></p>
    <p class="entree-field"><strong>Essieux:</strong> <span id="r_essieux"></span></p>
    <p class="entree-field"><strong>Observation:</strong> <span id="r_observation"></span></p>
    <p class="entree-field"><strong>Enregistré par:</strong> <span id="r_user"></span></p>
    <p class="entree-field"><strong>Durée (jours calculés):</strong> <span id="r_days"></span></p>
  </div>

  <form id="factForm" method="POST" action="{{ route('facturations.createFromEntree') }}">
    @csrf
    <input type="hidden" name="entree_id" id="entree_id">
    <div class="mb-3">
      <label class="form-label">Catégorie facture</label>
      <select id="categorie_id" name="categorie_id" class="form-select" required>
        <option value="">-- Choisir catégorie --</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" data-price="{{ $c->prix_par_24h }}">{{ $c->nom }} ({{ $c->prix_par_24h }})</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Jours calculés</label>
      <input id="input_days" type="number" name="duree" class="form-control" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">Montant total</label>
      <input id="input_total" type="text" name="montant_total" class="form-control" readonly>
    </div>
    @if($canReduce)
    <div class="mb-3">
      <label class="form-label">Réduction (montant)</label>
      <input id="input_reduction" type="number" step="0.01" min="0" name="reduction" class="form-control" value="0">
    </div>
    @endif
    <div class="mb-3">
      <label class="form-label">Montant payé</label>
      <input id="input_paye" type="number" step="0.01" min="0" name="montant_paye" class="form-control" value="0">
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">Créer Facture</button>
      <a href="{{ route('facturations.index') }}" class="btn btn-secondary">Annuler</a>
    </div>
  </form>
</div>

<div id="notFound" class="alert alert-warning" style="display:none">
  Aucune entrée trouvée pour cette plaque.
</div>

<script>
function doPlaqueLookup(plaque) {
  if (!plaque) return;
  fetch("{{ route('facturations.findByPlaque') }}?plaque=" + encodeURIComponent(plaque))
    .then(r => r.json())
    .then(data => {
      if (!data.found) {
        document.getElementById('resultArea').style.display = 'none';
        document.getElementById('notFound').style.display = 'block';
        return;
      }
      document.getElementById('notFound').style.display = 'none';
      const e = data.entree;
      document.getElementById('resultArea').style.display = 'block';
      document.getElementById('r_plaque').textContent = e.vehicule.plaque ?? '';
      document.getElementById('r_compagnie').textContent = e.vehicule.compagnie ?? '';
      document.getElementById('r_client').textContent = e.client ? e.client.nom : '';
      document.getElementById('r_date_entree').textContent = e.date_entree;
      document.getElementById('r_marque').textContent = e.vehicule.marque ?? '';
      document.getElementById('r_pays').textContent = e.vehicule.pays ?? '';
      document.getElementById('r_essieux').textContent = e.vehicule.essieux ?? '';
      document.getElementById('r_observation').textContent = e.observation ?? '';
      document.getElementById('r_user').textContent = e.user ? e.user.name : '';
      document.getElementById('r_days').textContent = data.days;
      document.getElementById('entree_id').value = e.id;
      document.getElementById('input_days').value = data.days;
      // reset totals
      document.getElementById('categorie_id').value = '';
      document.getElementById('input_total').value = '';
      @if($canReduce)
        document.getElementById('input_reduction').value = 0;
      @endif
    });
}

document.getElementById('btnSearch').addEventListener('click', function(e){
  e.preventDefault();
  doPlaqueLookup(document.getElementById('searchPlaque').value.trim());
});

// lookup on Enter
document.getElementById('searchPlaque').addEventListener('keydown', function(e){
  if (e.key === 'Enter') { e.preventDefault(); doPlaqueLookup(this.value.trim()); }
});

// lookup on blur
document.getElementById('searchPlaque').addEventListener('blur', function(){
  doPlaqueLookup(this.value.trim());
});

// toggle show/hide entree fields via icon in header
document.getElementById('toggleInfoIcon').addEventListener('click', function(e){
  e.preventDefault();
  const fields = document.querySelectorAll('.entree-field');
  const anyHidden = Array.from(fields).some(f => f.style.display === 'none');
  fields.forEach(f => { f.style.display = anyHidden ? '' : 'none'; });
  const ic = this.querySelector('i');
  if (ic) ic.className = anyHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
});

// If a plaque is provided via query param, auto-run lookup on load
document.addEventListener('DOMContentLoaded', function(){
  const initial = '{{ request()->get('plaque', '') }}'.trim();
  if (initial) {
    document.getElementById('searchPlaque').value = initial;
    doPlaqueLookup(initial);
  }
});

// compute total when category changes or reduction changes
function computeTotal() {
  const sel = document.getElementById('categorie_id');
  const days = Number(document.getElementById('input_days').value) || 1;
  if (!sel.value) { document.getElementById('input_total').value = ''; return; }
  const price = Number(sel.options[sel.selectedIndex].dataset.price) || 0;
  let total = days * price;
  @if($canReduce)
    const red = Number(document.getElementById('input_reduction').value) || 0;
    if (red > 0) total = Math.max(0, total - red);
  @endif
  document.getElementById('input_total').value = total.toFixed(2);
  // clamp montant_paye to not exceed total
  const payEl = document.getElementById('input_paye');
  if (payEl) {
    let pay = Number(payEl.value) || 0;
    if (pay > total) pay = total;
    payEl.value = pay.toFixed(2);
  }
}

document.getElementById('categorie_id').addEventListener('change', computeTotal);
@if($canReduce)
  document.getElementById('input_reduction').addEventListener('input', computeTotal);
@endif
// ensure paid amount does not exceed computed total when user edits it
const payEl = document.getElementById('input_paye');
if (payEl) {
  payEl.addEventListener('input', function(){
    const total = Number(document.getElementById('input_total').value) || 0;
    let v = Number(this.value) || 0;
    if (v > total) this.value = total.toFixed(2);
  });
}
</script>

@endsection
