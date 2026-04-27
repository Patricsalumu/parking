@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center mb-3">
  <a href="{{ route('journal_comptes.index') }}" class="btn btn-sm btn-outline-secondary me-2">Retour</a>
  <h3 class="mb-0">Nouvelle écriture comptable</h3>
</div>

<div class="card p-3">
  <form id="jc_form" method="post" action="{{ route('journal_comptes.store') }}">
    @csrf
    <div id="form_alert" class="alert alert-danger d-none"></div>
    <div class="mb-3">
      <label class="form-label">Libellé</label>
      <input name="libelle" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Compte à débiter (numéro ou nom)</label>
      <input id="searchDebit" name="search_debit" type="text" class="form-control" placeholder="Saisir numéro ou nom" value="{{ old('search_debit') }}">
      <input id="compte_debit_id" name="compte_debit_id" type="hidden" value="{{ old('compte_debit_id') }}">
      <datalist id="comptes_list_debit"></datalist>
      @if($errors->has('compte_debit_id')) <div class="text-danger small mt-1">{{ $errors->first('compte_debit_id') }}</div> @endif
    </div>
    <div class="mb-3">
      <label class="form-label">Compte à créditer (numéro ou nom)</label>
      <input id="searchCredit" name="search_credit" type="text" class="form-control" placeholder="Saisir numéro ou nom" value="{{ old('search_credit') }}">
      <input id="compte_credit_id" name="compte_credit_id" type="hidden" value="{{ old('compte_credit_id') }}">
      <datalist id="comptes_list_credit"></datalist>
      @if($errors->has('compte_credit_id')) <div class="text-danger small mt-1">{{ $errors->first('compte_credit_id') }}</div> @endif
    </div>
    <div class="mb-3">
      <label class="form-label">Montant</label>
      <input name="montant" type="number" step="0.01" min="0" class="form-control" required>
    </div>
    
    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">Créer</button>
      <a href="{{ route('journal_comptes.index') }}" class="btn btn-secondary">Annuler</a>
    </div>
  </form>
</div>

@endsection

@push('scripts')
<script>
;(function(){
  function setupLookup(inputId, datalistId, hiddenId){
    const input = document.getElementById(inputId);
    const dl = document.getElementById(datalistId);
    input.setAttribute('list', datalistId);
    let suggestions = {};
    let debounce = null;
    input.addEventListener('input', function(){
      const q = this.value.trim();
      if (debounce) clearTimeout(debounce);
      if (!q) { dl.innerHTML = ''; suggestions = {}; document.getElementById(hiddenId).value = ''; return; }
      debounce = setTimeout(()=>{
        fetch('{{ route('journal_comptes.search_comptes') }}?q=' + encodeURIComponent(q), {credentials:'same-origin'})
          .then(r => r.ok ? r.json() : Promise.reject())
          .then(data => {
            dl.innerHTML = '';
            suggestions = {};
            (data.results || []).forEach(item => {
              const opt = document.createElement('option'); opt.value = item.numero + ' - ' + item.nom; opt.textContent = opt.value;
              dl.appendChild(opt);
              suggestions[opt.value] = item;
            });
            if (q && suggestions[q]) {
              document.getElementById(hiddenId).value = suggestions[q].id;
            }
          }).catch(()=>{ dl.innerHTML = ''; suggestions = {}; });
      }, 200);
    });
    input.addEventListener('change', function(){
      const v = this.value.trim();
      const item = suggestions[v];
      if (item) document.getElementById(hiddenId).value = item.id;
    });
  }
  setupLookup('searchDebit','comptes_list_debit','compte_debit_id');
  setupLookup('searchCredit','comptes_list_credit','compte_credit_id');
  // client-side submit validation to avoid round-trips
  const form = document.getElementById('jc_form');
  const alertBox = document.getElementById('form_alert');
  form.addEventListener('submit', function(e){
    alertBox.classList.add('d-none');
    alertBox.textContent = '';
    const debitId = document.getElementById('compte_debit_id').value || '';
    const creditId = document.getElementById('compte_credit_id').value || '';
    if (!debitId) {
      e.preventDefault();
      alertBox.textContent = 'Veuillez sélectionner un compte de débit valide depuis la liste.';
      alertBox.classList.remove('d-none');
      return;
    }
    if (!creditId) {
      e.preventDefault();
      alertBox.textContent = 'Veuillez sélectionner un compte de crédit valide depuis la liste.';
      alertBox.classList.remove('d-none');
      return;
    }
    if (debitId === creditId) {
      e.preventDefault();
      alertBox.textContent = 'Le compte de débit et le compte de crédit doivent être différents.';
      alertBox.classList.remove('d-none');
      return;
    }
  });
})();
</script>
@endpush
