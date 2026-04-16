@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-md-8">
    <div class="d-flex justify-content-between mb-3">
      <h3>Modifier Entrée</h3>
      <div><a href="{{ route('entrees.index') }}" class="btn btn-secondary">Retour</a></div>
    </div>

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
      <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Erreur</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <ul>
                @foreach($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
          </div>
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('entrees.update', $entree) }}">
  @csrf @method('PUT')

  <div class="mb-3">
    <label>Plaque <span class="text-danger">*</span></label>
    <div class="input-group">
      <input name="plaque" id="plaque" value="{{ old('plaque', $entree->vehicule->plaque ?? '') }}" class="form-control" placeholder="Plate number" required>
      <button type="button" id="btn_lookup" class="btn btn-outline-secondary" title="Rechercher la plaque"><i class="bi bi-search"></i></button>
    </div>
    <input type="hidden" name="vehicule_id" id="vehicule_id" value="{{ old('vehicule_id', $entree->vehicule_id) }}">
    <div class="mt-1"><span id="plaque_status" class="badge bg-secondary">Recherche inactive</span></div>
    @error('plaque') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label>Compagnie <span class="text-danger">*</span></label>
      <input name="compagnie" id="compagnie" value="{{ old('compagnie', $entree->vehicule->compagnie ?? '') }}" class="form-control" placeholder="Company / carrier" required>
    </div>
    <div class="col-md-6 mb-3">
      <label>Pays <span class="text-danger">*</span></label>
      @php
        $paysList = [
          'Algérie','Angola','Bénin','Botswana','Burkina Faso','Burundi','Cabo Verde','Cameroun','République centrafricaine','Tchad','Comores','République du Congo','RDC','Côte d\'Ivoire','Djibouti','Égypte','Guinée équatoriale','Érythrée','Eswatini','Éthiopie','Gabon','Gambie','Ghana','Guinée','Guinée-Bissau','Kenya','Lesotho','Libéria','Libye','Madagascar','Malawi','Mali','Mauritanie','Maurice','Maroc','Mozambique','Namibie','Niger','Nigeria','Rwanda','Sao Tomé-et-Principe','Sénégal','Seychelles','Sierra Leone','Somalie','Afrique du Sud','Soudan du Sud','Soudan','Tanzanie','Togo','Tunisie','Ouganda','Zambie','Zimbabwe'
        ];
        sort($paysList, SORT_STRING);
      @endphp
      <input name="pays" id="pays" list="pays_list" class="form-control" value="{{ old('pays', $entree->vehicule->pays ?? '') }}" placeholder="Pays" required>
      <datalist id="pays_list">
        @foreach($paysList as $p)
          <option value="{{ $p }}">{{ $p }}</option>
        @endforeach
      </datalist>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label>Marque</label>
      @php
        $marques = ['HOWO','CANTER','FUZO','TRUCK','MERCEDES','VOLVO','MAN','DAF','SCANIA','IVECO','RENAULT','HINO','FOTON','MITSUBISHI FUSO','AUTRES'];
      @endphp
      <input name="marque" id="marque" list="marques_list" class="form-control" value="{{ old('marque', $entree->vehicule->marque ?? '') }}" placeholder="Marque">
      <datalist id="marques_list">
        @foreach($marques as $m)
          <option value="{{ $m }}">{{ $m }}</option>
        @endforeach
      </datalist>
    </div>
    <div class="col-md-6 mb-3">
      <label>Essieux</label>
      <select name="essieux" id="essieux" class="form-select">
        <option value="">-- Choisir --</option>
        @for($i=2;$i<=8;$i++)
          <option value="{{ $i }}" {{ (int) old('essieux', $entree->vehicule->essieux ?? '') === $i ? 'selected' : '' }}>{{ $i }}</option>
        @endfor
      </select>
    </div>
  </div>

  <h5>Client (auto-filled if plaque exists)</h5>
  <input type="hidden" name="client_id" id="client_id" value="{{ old('client_id', $entree->client_id) }}">
  <div class="mb-3"><label>Nom du client</label><input name="client_nom" id="client_nom" value="{{ old('client_nom', $entree->client->nom ?? '') }}" class="form-control" placeholder="Client name"></div>

  <div class="mb-3"><label>Observation</label><textarea name="observation" class="form-control">{{ old('observation', $entree->observation) }}</textarea></div>
  <div class="mb-3">
    <label>Catégorie</label>
    @isset($categories)
      <select name="categorie_id" id="categorie_id" class="form-select">
        <option value="">-- Choisir une catégorie --</option>
        @foreach($categories as $categorie)
          <option value="{{ $categorie->id }}" {{ (int) old('categorie_id', $entree->categorie_id) === $categorie->id ? 'selected' : '' }}>{{ $categorie->nom ?? $categorie->libelle ?? $categorie->designation ?? ('Catégorie ' . $categorie->id) }}</option>
        @endforeach
      </select>
    @else
      <input type="hidden" name="categorie_id" value="{{ old('categorie_id', $entree->categorie_id) }}">
      <div class="form-text">Aucune liste de catégories fournie.</div>
    @endisset
    @error('categorie_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>

  <button class="btn btn-success">Enregistrer</button>
  <button type="reset" class="btn btn-secondary ms-2">Réinitialiser</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const plaqueInput = document.getElementById('plaque');
  const lookupBtn = document.getElementById('btn_lookup');
  const statusBadge = document.getElementById('plaque_status');

  plaqueInput.addEventListener('blur', fetchByPlaque);
  lookupBtn.addEventListener('click', fetchByPlaque);

  function setStatus(text, cls){
    statusBadge.textContent = text;
    statusBadge.className = 'badge ' + cls;
  }

  function fetchByPlaque(){
    const plaque = plaqueInput.value.trim();
    if (!plaque) {
      setStatus('Aucune plaque', 'bg-secondary');
      return;
    }
    setStatus('Recherche...', 'bg-info');
    fetch("{{ route('vehicules.findByPlaque') }}?plaque="+encodeURIComponent(plaque), {credentials: 'same-origin'})
      .then(r=>{
        if (!r.ok) {
          if (r.status === 401) { setStatus('Non autorisé', 'bg-danger'); return Promise.reject(new Error('unauthorized')); }
          return r.text().then(t=>{ throw new Error('Invalid response: '+t); });
        }
        const ct = r.headers.get('content-type') || '';
        if (!ct.includes('application/json')) { return r.text().then(t=>{ throw new Error('Non-JSON response: '+t); }); }
        return r.json();
      })
      .then(data=>{
        if (!data.found) { document.getElementById('vehicule_id').value = ''; document.getElementById('client_id').value = ''; setStatus('Non trouvé', 'bg-warning'); return; }
        const v = data.vehicule;
        document.getElementById('vehicule_id').value = v.id;
        document.getElementById('compagnie').value = v.compagnie || '';
        document.getElementById('marque').value = v.marque || '';
        document.getElementById('pays').value = v.pays || '';
        document.getElementById('essieux').value = v.essieux || '';
        if (v.client) { document.getElementById('client_id').value = v.client.id; document.getElementById('client_nom').value = v.client.nom || ''; }
        setStatus('Données trouvées', 'bg-success');
      }).catch((err)=>{ console.error('Plaque lookup error:', err); setStatus('Erreur', 'bg-danger'); });
  }
});
</script>

<!-- typeahead provided by native datalist for pays and marques -->

@if($errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', function(){ try { var modalEl = document.getElementById('errorModal'); if (modalEl) { var bsModal = new bootstrap.Modal(modalEl); bsModal.show(); } } catch(e) { console.error('Modal show failed', e); } });
  </script>
@endif

@endsection
