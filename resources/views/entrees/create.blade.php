@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-md-8">
    <h3>Nouvelle Entrée</h3>

    @if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
  <!-- Error modal -->
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

  <form method="POST" action="{{ route('entrees.store') }}">
  @csrf

  <div class="mb-3">
    <label>Plaque <span class="text-danger">*</span></label>
    <div class="input-group">
      <input name="plaque" id="plaque" value="{{ old('plaque') }}" class="form-control" placeholder="Plate number" required>
      <button type="button" id="btn_lookup" class="btn btn-outline-secondary" title="Rechercher la plaque"><i class="bi bi-search"></i></button>
    </div>
    @error('plaque') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    <input type="hidden" name="vehicule_id" id="vehicule_id" value="{{ old('vehicule_id') }}">
    <div class="mt-1"><span id="plaque_status" class="badge bg-secondary">Recherche inactive</span></div>
  </div>

  <div class="mb-3">
    <label>Compagnie <span class="text-danger">*</span></label>
    <input name="compagnie" id="compagnie" value="{{ old('compagnie') }}" class="form-control" placeholder="Company / carrier" required>
  </div>

  <div class="mb-3">
    <label>Marque</label>
    @php
      $marques = ['HOWO','CANTER','FUZO','TRUCK','MERCEDES','VOLVO','MAN','DAF','SCANIA','IVECO','RENAULT','HINO','FOTON','MITSUBISHI FUSO','AUTRES'];
    @endphp
    <input name="marque" id="marque" list="marques_list" class="form-control" value="{{ old('marque') }}" placeholder="Marque">
    <datalist id="marques_list">
      @foreach($marques as $m)
        <option value="{{ $m }}">{{ $m }}</option>
      @endforeach
    </datalist>
  </div>

  <div class="mb-3">
    <label>Pays <span class="text-danger">*</span></label>
    @php
      $paysList = [
        'Algérie','Angola','Bénin','Botswana','Burkina Faso','Burundi','Cabo Verde','Cameroun','République centrafricaine','Tchad','Comores','République du Congo','RDC','Côte d\'Ivoire','Djibouti','Égypte','Guinée équatoriale','Érythrée','Eswatini','Éthiopie','Gabon','Gambie','Ghana','Guinée','Guinée-Bissau','Kenya','Lesotho','Libéria','Libye','Madagascar','Malawi','Mali','Mauritanie','Maurice','Maroc','Mozambique','Namibie','Niger','Nigeria','Rwanda','Sao Tomé-et-Principe','Sénégal','Seychelles','Sierra Leone','Somalie','Afrique du Sud','Soudan du Sud','Soudan','Tanzanie','Togo','Tunisie','Ouganda','Zambie','Zimbabwe'
      ];
      sort($paysList, SORT_STRING);
    @endphp
    <input name="pays" id="pays" list="pays_list" class="form-control" value="{{ old('pays') }}" placeholder="Pays" required>
    <datalist id="pays_list">
      @foreach($paysList as $p)
        <option value="{{ $p }}">{{ $p }}</option>
      @endforeach
    </datalist>
  </div>

  <div class="mb-3">
    <label>Essieux</label>
    <select name="essieux" id="essieux" class="form-select">
      <option value="">-- Choisir --</option>
      @for($i=2;$i<=8;$i++)
        <option value="{{ $i }}" {{ old('essieux') == $i ? 'selected' : '' }}>{{ $i }}</option>
      @endfor
    </select>
  </div>

  <h5>Client</h5>
  <input type="hidden" name="client_id" id="client_id" value="{{ old('client_id') }}">
  <div class="mb-3">
    <label>Nom du client</label>
    <input name="client_nom" id="client_nom" list="clients_list" class="form-control" value="{{ old('client_nom') }}" placeholder="Client name (tapez pour suggestions)">
    <datalist id="clients_list">
      @foreach($clients as $c)
        <option value="{{ $c->nom }}"></option>
      @endforeach
    </datalist>
  </div>

  <div class="mb-3"><label>Observation</label><textarea name="observation" class="form-control">{{ old('observation') }}</textarea></div>

  <div class="mb-3">
    <label>Catégorie <span class="text-danger">*</span></label>
    @isset($categories)
      <select name="categorie_id" id="categorie_id" class="form-select" required>
        <option value="">-- Choisir une catégorie --</option>
        @foreach($categories as $categorie)
          <option value="{{ $categorie->id }}" {{ old('categorie_id') == $categorie->id ? 'selected' : '' }}>{{ $categorie->nom ?? $categorie->libelle ?? $categorie->designation ?? ('Catégorie ' . $categorie->id) }}</option>
        @endforeach
      </select>
    @else
      <input type="hidden" name="categorie_id" value="{{ old('categorie_id') }}">
      <div class="form-text">Aucune liste de catégories fournie.</div>
    @endisset
    @error('categorie_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
  </div>
  <button class="btn btn-success">Enregistrer l'entrée</button>
  <button type="reset" class="btn btn-secondary ms-2">Réinitialiser</button>
    </form>

  </div>
</div>

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
          if (r.status === 401) {
            setStatus('Non autorisé', 'bg-danger');
            console.error('Unauthorized request to vehicules.findByPlaque');
            return Promise.reject(new Error('unauthorized'));
          }
          return r.text().then(t=>{ throw new Error('Invalid response: '+t); });
        }
        const ct = r.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
          return r.text().then(t=>{ throw new Error('Non-JSON response: '+t); });
        }
        return r.json();
      })
      .then(data=>{
        if (!data.found) {
          // clear all linked vehicle and client fields
          document.getElementById('vehicule_id').value = '';
          document.getElementById('compagnie').value = '';
          document.getElementById('marque').value = '';
          document.getElementById('pays').value = '';
          document.getElementById('essieux').value = '';
          document.getElementById('client_id').value = '';
          document.getElementById('client_nom').value = '';
          setStatus('Non trouvé', 'bg-warning');
          window.lastFetchedPlaque = '';
          return;
        }
        const v = data.vehicule;
        // fill vehicle fields but DO NOT auto-fill client (user must choose or type)
        document.getElementById('vehicule_id').value = v.id;
        document.getElementById('compagnie').value = v.compagnie || '';
        document.getElementById('marque').value = v.marque || '';
        document.getElementById('pays').value = v.pays || '';
        document.getElementById('essieux').value = v.essieux || '';
        // clear client selection so user explicitly picks or types
        document.getElementById('client_id').value = '';
        document.getElementById('client_nom').value = '';
        window.lastFetchedPlaque = plaque;
        setStatus('Données trouvées', 'bg-success');
      }).catch((err)=>{
        console.error('Plaque lookup error:', err);
        setStatus('Erreur', 'bg-danger');
      });
  }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function(){
  // map of client name -> id for quick lookup
  const clientsMap = @json($clients->pluck('id','nom'));
  const clientInput = document.getElementById('client_nom');
  const clientIdFld = document.getElementById('client_id');
  const plaqueInput = document.getElementById('plaque');
  window.lastFetchedPlaque = window.lastFetchedPlaque || '';

  // when user types client name, if it exactly matches a known client set client_id, otherwise clear it
  if (clientInput) {
    clientInput.addEventListener('input', function(){
      const v = this.value || '';
      if (clientsMap[v] !== undefined) {
        clientIdFld.value = clientsMap[v];
      } else {
        clientIdFld.value = '';
      }
    });
  }

  // if user edits plaque after a successful lookup, clear prefilled vehicle/client data
  if (plaqueInput) {
    plaqueInput.addEventListener('input', function(){
      const val = this.value.trim();
      if (window.lastFetchedPlaque && val !== window.lastFetchedPlaque) {
        // clear vehicle and client linked fields
        document.getElementById('vehicule_id').value = '';
        document.getElementById('compagnie').value = '';
        document.getElementById('marque').value = '';
        document.getElementById('pays').value = '';
        document.getElementById('essieux').value = '';
        document.getElementById('client_id').value = '';
        document.getElementById('client_nom').value = '';
        window.lastFetchedPlaque = '';
        document.getElementById('plaque_status').textContent = 'Recherche inactive';
        document.getElementById('plaque_status').className = 'badge bg-secondary';
      }
    });
  }
});
</script>

<!-- typeahead provided by native datalist for pays and marques -->

@if($errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      try {
        var modalEl = document.getElementById('errorModal');
        if (modalEl) {
          var bsModal = new bootstrap.Modal(modalEl);
          bsModal.show();
        }
      } catch(e) { console.error('Modal show failed', e); }
    });
  </script>
@endif
@endsection
