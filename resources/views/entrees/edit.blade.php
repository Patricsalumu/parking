@extends('layouts.app')

@section('content')
<h3>Modifier Entrée</h3>

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

  <div class="mb-3">
    <label>Compagnie <span class="text-danger">*</span></label>
    <input name="compagnie" id="compagnie" value="{{ old('compagnie', $entree->vehicule->compagnie ?? '') }}" class="form-control" placeholder="Company / carrier" required>
  </div>

  <div class="mb-3">
    <label>Marque</label>
    <input name="marque" id="marque" value="{{ old('marque', $entree->vehicule->marque ?? '') }}" class="form-control" placeholder="Vehicle make/model">
  </div>

  <div class="mb-3">
    <label>Pays <span class="text-danger">*</span></label>
    <input name="pays" id="pays" value="{{ old('pays', $entree->vehicule->pays ?? '') }}" class="form-control" placeholder="Country" required>
  </div>

  <div class="mb-3">
    <label>Essieux</label>
    <input type="number" name="essieux" id="essieux" value="{{ old('essieux', $entree->vehicule->essieux ?? '') }}" class="form-control" placeholder="Number of axles">
  </div>

  <h5>Client (auto-filled if plaque exists)</h5>
  <input type="hidden" name="client_id" id="client_id" value="{{ old('client_id', $entree->client_id) }}">
  <div class="mb-3"><label>Nom du client</label><input name="client_nom" id="client_nom" value="{{ old('client_nom', $entree->client->nom ?? '') }}" class="form-control" placeholder="Client name"></div>

  <div class="mb-3"><label>Observation</label><textarea name="observation" class="form-control">{{ old('observation', $entree->observation) }}</textarea></div>
  <div class="mb-3"><label>QR Code (optional)</label><input name="qr_code" class="form-control" value="{{ old('qr_code', $entree->qr_code) }}"></div>

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

@if($errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', function(){ try { var modalEl = document.getElementById('errorModal'); if (modalEl) { var bsModal = new bootstrap.Modal(modalEl); bsModal.show(); } } catch(e) { console.error('Modal show failed', e); } });
  </script>
@endif

@endsection
