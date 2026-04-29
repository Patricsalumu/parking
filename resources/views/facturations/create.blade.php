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

<div id="resultArea" style="display:block">
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
    <p class="entree-field"><strong>Durée:</strong> <span id="r_days"></span></p>
    <p class="entree-field"><strong>Facturé par:</strong> <span id="r_fact_user"></span></p>
  </div>

  <form id="factForm" method="POST" action="{{ route('facturations.createFromEntree') }}">
    @csrf
    <input type="hidden" name="entree_id" id="entree_id">
    <div class="mb-3">
      <label class="form-label">Catégorie facture (depuis l'entrée)</label>
      <select id="categorie_id" name="categorie_select" class="form-select" required>
        <option value="">-- Catégorie depuis entrée --</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" data-price="{{ $c->prix_par_24h }}">{{ $c->nom }} ({{ $c->prix_par_24h }})</option>
        @endforeach
      </select>
      <input type="hidden" id="categorie_id_input" name="categorie_id" value="">
      <div class="form-text">Si l'entrée a une catégorie elle sera utilisée et verrouillée.</div>
      
    </div>
    <div class="mb-3">
      <label class="form-label">Jours calculés</label>
      <input id="input_days" type="number" name="duree" class="form-control" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">Montant total</label>
      <input id="input_total" type="text" name="montant_total" class="form-control" readonly>
      <div id="billingAlert" class="mt-2"></div>
    </div>
    <div class="mb-3">
      <label class="form-label">Réduction (montant)</label>
      <input id="input_reduction" type="number" step="0.01" min="0" name="reduction" class="form-control" value="0">
    </div>
    <div class="mb-3">
      <label class="form-label">Net à payer</label>
      <input id="input_net" type="text" class="form-control" readonly>
    </div>
    <div class="mb-3" id="div_deja_paye" style="display:none">
      <label class="form-label">Déjà payé</label>
      <input id="input_deja_paye" type="text" class="form-control" readonly>
    </div>
    <div class="mb-3">
      <label class="form-label">Montant payé</label>
      <input id="input_paye" type="number" step="0.01" min="0" name="montant_paye" class="form-control" value="0">
    </div>
    <div class="mb-3">
      <label class="form-label">Reste à payer</label>
      <input id="input_reste" type="text" class="form-control" readonly>
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
// format ISO datetime string to `YYYY-MM-DD HH:mm`
function formatDateTime(iso) {
  try {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return iso;
    const pad = v => String(v).padStart(2,'0');
    const Y = d.getFullYear();
    const M = pad(d.getMonth()+1);
    const D = pad(d.getDate());
    const h = pad(d.getHours());
    const m = pad(d.getMinutes());
    return `${Y}-${M}-${D} ${h}:${m}`;
  } catch(e) { return iso; }
}
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
      document.getElementById('r_date_entree').textContent = formatDateTime(e.date_entree);
      document.getElementById('r_marque').textContent = e.vehicule.marque ?? '';
      document.getElementById('r_pays').textContent = e.vehicule.pays ?? '';
      document.getElementById('r_essieux').textContent = e.vehicule.essieux ?? '';
      document.getElementById('r_observation').textContent = e.observation ?? '';
      document.getElementById('r_user').textContent = e.user ? e.user.name : '';
        // duration: show days/hours/minutes
        const dur = data.duration || {days:0,hours:0,minutes:0};
        const durLabel = dur.days + 'j ' + dur.hours + 'h ' + dur.minutes + 'm';
        document.getElementById('r_days').textContent = durLabel;
      document.getElementById('entree_id').value = e.id;
      // compute displayed days as ceil(totalHours/24) with minimum 1
      const totalHours = (Number(dur.days) || 0) * 24 + (Number(dur.hours) || 0) + Math.floor((Number(dur.minutes) || 0) / 60);
      const computedDays = Math.max(1, Math.ceil(totalHours / 24));
      document.getElementById('input_days').value = computedDays;
      // reset totals
        // set category from entree if present and lock it
        if (e.categorie_id) {
          document.getElementById('categorie_id').value = e.categorie_id;
          document.getElementById('categorie_id').disabled = true;
          document.getElementById('categorie_id_input').value = e.categorie_id;
        } else {
          document.getElementById('categorie_id').value = '';
          document.getElementById('categorie_id').disabled = false;
          document.getElementById('categorie_id_input').value = '';
        }
        document.getElementById('input_total').value = '';
        document.getElementById('input_reduction').value = 0;
        document.getElementById('input_net').value = '';
        document.getElementById('input_paye').value = 0;
        document.getElementById('input_paye').disabled = false;
        document.getElementById('input_reduction').disabled = false;
        // set submit state depending on explicit `sortie` boolean (fallback to payload flag)
        const submitBtn = document.querySelector('#factForm button[type=submit]');
        const entreeSortieFlag = (typeof e.sortie !== 'undefined') ? !!e.sortie : !!data.entree_closed;
        if (submitBtn) submitBtn.disabled = entreeSortieFlag;
        // store current duration, entry date and existing payment for client-side rule display
        window._currentDuration = dur;
        window._currentEntryDate = e.date_entree;
        window._hasExistingFacturation = !!data.facturation;
        window._dejaPayé = data.facturation ? Number(data.facturation.montant_paye ?? 0) : 0;
        // show/hide and populate the "Déjà payé" field
        const dejaDiv = document.getElementById('div_deja_paye');
        if (dejaDiv) {
          if (window._dejaPayé > 0) {
            dejaDiv.style.display = '';
            document.getElementById('input_deja_paye').value = window._dejaPayé.toFixed(2);
          } else {
            dejaDiv.style.display = 'none';
            document.getElementById('input_deja_paye').value = '0';
          }
        }

        // if an existing facture exists, prefill reduction and lock it, then recompute
        if (data.facturation) {
          const f = data.facturation;
          document.getElementById('input_reduction').value = f.reduction ?? 0;
          document.getElementById('input_reduction').disabled = true;
          const entreeClosed = (data.entree && typeof data.entree.sortie !== 'undefined') ? !!data.entree.sortie : !!data.entree_closed;
          document.getElementById('input_paye').disabled = entreeClosed;
          document.getElementById('r_fact_user').textContent = f.user_name || '';
          if (typeof showBillingAlerts === 'function') showBillingAlerts();
          if (typeof computeTotal === 'function') computeTotal();
          const submitBtnF = document.querySelector('#factForm button[type=submit]');
          if (submitBtnF) submitBtnF.disabled = entreeClosed;
        } else {
          document.getElementById('r_fact_user').textContent = '';
          if (typeof showBillingAlerts === 'function') showBillingAlerts();
          if (typeof computeTotal === 'function') computeTotal();
        }
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

// dynamic suggestions for plaques (like index page)
;(function(){
  const plaqueInput = document.getElementById('searchPlaque');
  if (!plaqueInput) return;
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
    if (!q) { dl.innerHTML = ''; window._plaqueSuggestions = {}; return; }
    debounceTimer = setTimeout(()=>{
      fetch("{{ route('vehicules.searchPlaques') }}?q="+encodeURIComponent(q), {credentials:'same-origin'})
        .then(r=> r.ok ? r.json() : Promise.reject())
            .then(data=>{
              dl.innerHTML = '';
              window._plaqueSuggestions = {};
              (data.results || []).forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.plaque;
                opt.textContent = [item.plaque, item.client, item.compagnie, item.pays].filter(Boolean).join(' — ');
                dl.appendChild(opt);
                window._plaqueSuggestions[item.plaque] = item;
              });
              // if the current query exactly matches a known suggestion, trigger immediate lookup
              if (q && window._plaqueSuggestions[q]) {
                doPlaqueLookup(q);
              }
            }).catch(()=>{ dl.innerHTML = ''; window._plaqueSuggestions = {}; });
    }, 200);
  });

  // when a suggestion is selected, trigger lookup
  plaqueInput.addEventListener('change', function(){
    const v = this.value.trim();
    if (!v) return;
    const item = (window._plaqueSuggestions || {})[v];
    if (item) {
      // run the existing lookup to populate the create form
      doPlaqueLookup(v);
    }
  });
})();

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
  else {
    // try to prefill with latest open entree (if any)
    try {
      fetch("{{ route('facturations.latestOpenEntree') }}", { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (!data.found) return;
          const e = data.entree;
          const safe = id => document.getElementById(id);
          if (safe('resultArea')) safe('resultArea').style.display = 'block';
          if (safe('r_plaque')) safe('r_plaque').textContent = e.vehicule.plaque ?? '';
          if (safe('r_compagnie')) safe('r_compagnie').textContent = e.vehicule.compagnie ?? '';
          if (safe('r_client')) safe('r_client').textContent = e.client ? e.client.nom : '';
          if (safe('r_date_entree')) safe('r_date_entree').textContent = formatDateTime(e.date_entree);
          if (safe('r_marque')) safe('r_marque').textContent = e.vehicule.marque ?? '';
          if (safe('r_pays')) safe('r_pays').textContent = e.vehicule.pays ?? '';
          if (safe('r_essieux')) safe('r_essieux').textContent = e.vehicule.essieux ?? '';
          if (safe('r_observation')) safe('r_observation').textContent = e.observation ?? '';
          if (safe('r_user')) safe('r_user').textContent = e.user ? e.user.name : '';
          const dur = data.duration || {days:0,hours:0,minutes:0};
          const durLabel = dur.days + 'j ' + dur.hours + 'h ' + dur.minutes + 'm';
          if (safe('r_days')) safe('r_days').textContent = durLabel;
          if (safe('entree_id')) safe('entree_id').value = e.id;
          if (e.categorie_id && safe('categorie_id')) {
            safe('categorie_id').value = e.categorie_id;
            safe('categorie_id').disabled = true;
            if (safe('categorie_id_input')) safe('categorie_id_input').value = e.categorie_id;
          }
          window._currentDuration = dur;
          window._currentEntryDate = e.date_entree;
          if (typeof showBillingAlerts === 'function') showBillingAlerts();
          if (typeof computeTotal === 'function') computeTotal();
        }).catch(()=>{});
    } catch(e) { /* ignore */ }
  }
});

// compute total when category changes or reduction changes
function computeTotal() {
  const sel = document.getElementById('categorie_id');
  const days = Number(document.getElementById('input_days').value) || 1;
  if (!sel.value) { document.getElementById('input_total').value = ''; return; }
  const price = Number(sel.options[sel.selectedIndex].dataset.price) || 0;
  const selectedCatId = Number(sel.value);
  // compute hours from detailed duration if present, else fallback to days*24
  let hours = days * 24;
  if (window._currentDuration) {
    hours = (Number(window._currentDuration.days) || 0) * 24 + (Number(window._currentDuration.hours) || 0);
  }
  let total = 0;
  if (hours <= 24) {
    total = price;
  } else {
    const remaining = hours - 24;
    const fullAdditionalDays = Math.floor(remaining / 24);
    const remainder = remaining % 24;
    total = price + fullAdditionalDays * price;
    if (remainder > 0) {
      if (selectedCatId === 2) {
        total += (remainder <= 5) ? (price * 0.5) : price;
      } else {
        total += price;
      }
    }
  }
  // special: category 1 pays only if stayed overnight (entry date != today)
  if (selectedCatId === 1 && window._currentEntryDate) {
    const entryDate = (new Date(window._currentEntryDate)).toISOString().slice(0,10);
    const today = (new Date()).toISOString().slice(0,10);
    if (entryDate === today) {
      total = 0;
    }
  }
  const red = Number(document.getElementById('input_reduction').value) || 0;
  const net = Math.max(0, total - red);
  document.getElementById('input_total').value = total.toFixed(2);
  document.getElementById('input_net').value = net.toFixed(2);
  // pre-fill montant_paye: for new facture = net; for refacturation = net - dejaPayé
  const payEl = document.getElementById('input_paye');
  if (payEl) {
    const dejaPayé = window._hasExistingFacturation ? (window._dejaPayé || 0) : 0;
    const maxPay = Math.max(0, net - dejaPayé);
    let pay;
    if (window._hasExistingFacturation) {
      pay = maxPay;
    } else {
      pay = net;
    }
    payEl.value = pay.toFixed(2);
    document.getElementById('input_reste').value = Math.max(0, net - dejaPayé - pay).toFixed(2);
  }
  if (typeof showBillingAlerts === 'function') showBillingAlerts();
}

function showBillingAlerts() {
  const alertEl = document.getElementById('billingAlert');
  if (!alertEl) return;
  alertEl.innerHTML = '';
  const sel = document.getElementById('categorie_id');
  if (!sel || !sel.value) return;
  const catId = Number(sel.value);
  // compute hours and remainder for last day
  let hours = (Number(window._currentDuration?.days) || 0) * 24 + (Number(window._currentDuration?.hours) || 0);
  const entryDate = window._currentEntryDate ? (new Date(window._currentEntryDate)).toISOString().slice(0,10) : null;
  const today = (new Date()).toISOString().slice(0,10);
  if (catId === 1 && entryDate && entryDate === today) {
    alertEl.innerHTML = '<div class="alert alert-warning small">Transbordement canter : pas de nuitée détectée → facturation = 0 &ndash; pas de paiement requis.</div>';
    return;
  }
  if (hours > 24) {
    const remaining = hours - 24;
    const remainder = remaining % 24;
    if (catId === 2 && remainder > 0 && remainder <= 5) {
      alertEl.innerHTML = '<div class="alert alert-info small">Transborment Truck : dernier jour partiel ≤ 5h → dernier jour facturé à 50%.</div>';
      return;
    }
  }
  alertEl.innerHTML = '';
}

document.getElementById('categorie_id').addEventListener('change', computeTotal);
// keep hidden input in sync with select for form submission
document.getElementById('categorie_id').addEventListener('change', function(){
  document.getElementById('categorie_id_input').value = this.value;
});
@if($canReduce)
  document.getElementById('input_reduction').addEventListener('input', computeTotal);
@endif
// ensure paid amount does not exceed computed total when user edits it
const payEl = document.getElementById('input_paye');
if (payEl) {
  payEl.addEventListener('input', function(){
    const net = Number(document.getElementById('input_net').value) || 0;
    const dejaPayé = window._hasExistingFacturation ? (window._dejaPayé || 0) : 0;
    const maxPay = Math.max(0, net - dejaPayé);
    let v = Number(this.value) || 0;
    if (v > maxPay) { this.value = maxPay.toFixed(2); v = maxPay; }
    document.getElementById('input_reste').value = Math.max(0, net - dejaPayé - v).toFixed(2);
  });
}
</script>

@endsection
