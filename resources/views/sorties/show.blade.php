<div class="modal-body">
  <div class="row">
    <div class="col-md-6">
      <div class="mb-2"><strong>Client:</strong> {{ $entree->client?->nom ?? 'N/C' }}</div>
      <div class="mb-2"><strong>Numero:</strong> {{ $entree->numero_formatted ?? $entree->numero ?? $entree->id }}</div>
      <div class="mb-2"><strong>Date entrée:</strong> {{ $entree->date_entree ? \Carbon\Carbon::parse($entree->date_entree)->format('Y-m-d H:i') : '' }}</div>
      <div class="mb-2"><strong>Utilisateur entrée:</strong> {{ $entree->user?->name }}</div>
      <div class="mb-2"><strong>Date sortie:</strong> {{ $entree->date_sortie ? \Carbon\Carbon::parse($entree->date_sortie)->format('Y-m-d H:i') : '-' }}</div>
      <div class="mb-2"><strong>Utilisateur sortie:</strong> {{ $entree->sortieUser?->name ?? '-' }}</div>
      <hr>
      <h6>Facturation</h6>
      @if($fact)
        <div><strong>Facture #</strong> {{ $fact->id }}</div>
        <div><strong>Catégorie:</strong> {{ $fact->categorie?->nom ?? $entree->categorie?->nom ?? 'N/C' }}</div>
        <div><strong>Duree (jours):</strong> {{ $fact->duree ?? $entree->durationInDays() ?? 'N/A' }}</div>
        <div><strong>Total:</strong> {{ number_format($fact->montant_total ?? 0,2) }}</div>
        <div><strong>Payé:</strong> {{ number_format($fact->montant_paye ?? 0,2) }}</div>
        <div><strong>Reste:</strong> {{ number_format( ($fact->montant_total - ($fact->montant_paye ?? 0)) ,2) }}</div>
        <div><strong>Dernière mise à jour facture:</strong> {{ $fact->updated_at ? \Carbon\Carbon::parse($fact->updated_at)->format('Y-m-d H:i') : '-' }}</div>
        @if(isset($sinceBilled) && $sinceBilled)
          <div><strong>Depuis facturation:</strong> <span class="{{ $entree->date_sortie ? 'text-danger' : '' }}">{{ $sinceBilled['days'] }}j {{ $sinceBilled['hours'] }}h {{ $sinceBilled['minutes'] }}m</span></div>
        @endif
      @else
        <div class="text-danger">Aucune facture associée à cette entrée.</div>
      @endif
    </div>
    <div class="col-md-6">
      <h6>Véhicule</h6>
      @if($entree->vehicule)
        <div class="mb-2 d-flex justify-content-between"><span><strong>Plaque:</strong></span><span>{{ $entree->vehicule->plaque }}</span></div>
        <div class="mb-2 d-flex justify-content-between"><span><strong>Compagnie:</strong></span><span>{{ $entree->vehicule->compagnie ?? '-' }}</span></div>
        <div class="mb-2 d-flex justify-content-between"><span><strong>Marque:</strong></span><span>{{ $entree->vehicule->marque ?? '-' }}</span></div>
        <div class="mb-2 d-flex justify-content-between"><span><strong>Pays:</strong></span><span>{{ $entree->vehicule->pays ?? '-' }}</span></div>
        <div class="mb-2 d-flex justify-content-between"><span><strong>Essieux:</strong></span><span>{{ $entree->vehicule->essieux ?? '-' }}</span></div>
      @else
        <div class="text-muted">Informations véhicule indisponibles</div>
      @endif
    </div>
  </div>
  </div>
  
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" onclick="returnToSortiesIndex()">Fermer</button>
  @if(!$entree->date_sortie)
    @php
      $factPaidOrZero = false;
      if(isset($fact)) {
        $factPaidOrZero = (($fact->montant_paye ?? 0) >= ($fact->montant_total ?? 0));
      }
      // determine apurer disabled: enabled when facture exists and is paid (apurer will perform sortie regardless of delay)
      $apurerDisabled = true;
      if (isset($fact) && $factPaidOrZero) {
        $apurerDisabled = false;
      }
      // determine if confirm sortie is allowed (paid and within 1h)
      $confirmDisabled = true;
      if(isset($fact) && $factPaidOrZero && isset($minutesSince) && $minutesSince <= 60) {
        $confirmDisabled = false;
      }
    @endphp

    {{-- Apurer button always visible, but disabled when not allowed --}}
    @if($apurerDisabled)
      <button class="btn btn-primary" disabled>Apurer</button>
    @else
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmApurerModal">Apurer</button>
    @endif

    @if(!$confirmDisabled)
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmSortieModal">Confirmer Sortie</button>
    @else
      @if(!$factPaidOrZero)
        <button class="btn btn-warning" disabled>Sortie non permise (facture non apurée)</button>
      @else
        <button class="btn btn-secondary" disabled>Confirmer Sortie (bloquée par délai >1h)</button>
      @endif
    @endif
  @endif
</div>

<script>
function returnToSortiesIndex(){
  // If loaded inside the index modal, hide it then go to index
  const outer = document.getElementById('sortieModal');
  if (outer) {
    try { bootstrap.Modal.getInstance(outer)?.hide(); } catch(e){}
  }
  window.location.href = "{{ route('sorties.index') }}";
}
</script>

<!-- Confirm Sortie Modal -->
<div class="modal fade" id="confirmSortieModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Confirmer Sortie</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">Voulez-vous vraiment enregistrer la sortie du véhicule <strong>{{ $entree->vehicule?->plaque }}</strong> ?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <form id="confirmSortieForm" method="POST" action="{{ route('sorties.update', $entree) }}" style="display:inline">
          @csrf
          @method('PUT')
          <button class="btn btn-danger">Confirmer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Confirm Apurer Modal -->
<div class="modal fade" id="confirmApurerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Confirmer Apurement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">Voulez-vous vraiment apurer (mettre à jour) la facture pour permettre la sortie du véhicule <strong>{{ $entree->vehicule?->plaque }}</strong> ?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <form id="confirmApurerForm" method="POST" action="{{ route('sorties.apurer', $entree) }}" style="display:inline" onsubmit="submitApurerForm(event,this)">
          @csrf
          <button type="submit" class="btn btn-primary">Apurer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Local debug: log when apurer form is submitted inside the modal
document.addEventListener('DOMContentLoaded', function(){
  try {
    const f = document.getElementById('confirmApurerForm');
    if (!f) return;
    f.addEventListener('submit', function(e){
      console.debug('Local: confirmApurerForm submit triggered for', this.action);
      // disable submit button to avoid double submits
      const btn = this.querySelector('button[type=submit]');
      if (btn) btn.disabled = true;
    });
  } catch(err) { console.error('confirmApurerForm local script error', err); }
});

function submitApurerForm(e, form){
  try {
    e.preventDefault();
    console.debug('Inline submitApurerForm', form.action);
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const btn = form.querySelector('button[type=submit]');
    if (btn) btn.disabled = true;
    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token || '',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({})
    }).then(r => {
      console.debug('Inline apurer response', r.status, r.headers.get('content-type'));
      return r.json();
    }).then(data => {
      console.debug('Inline apurer data', data);
      // close modal if present
      try { const outer = document.getElementById('sortieModal'); bootstrap.Modal.getInstance(outer)?.hide(); } catch(e){}
      // show toast if available
      try { if (window.showToast) window.showToast(data.message || 'Facture apurée', 'success'); } catch(e){}
      // try to update the table row in-place (if on index page)
      if (data && data.entree && data.entree.id) {
        try {
          const row = document.getElementById('entree-row-' + data.entree.id);
          if (row) {
            const dsCell = row.querySelector('.entree-date-sortie');
            if (dsCell) dsCell.textContent = data.entree.date_sortie || '-';
            const sbCell = row.querySelector('.entree-since-billed');
            if (sbCell) {
              sbCell.textContent = data.entree.sinceBilled || '-';
              sbCell.classList.add('text-danger');
            }
            const plaqueCell = row.querySelector('.entree-plaque');
            if (plaqueCell) plaqueCell.classList.add('text-decoration-line-through','text-muted');
            const actionsCell = row.querySelector('.entree-actions');
            if (actionsCell) actionsCell.innerHTML = '<button class="btn btn-sm btn-light" disabled>Déjà sorti</button>';
          }
        } catch(e){ console.error('Inline update row error', e); }
      }
      // if update not visible, reload as fallback
      setTimeout(function(){ try { window.location.reload(); } catch(e){} }, 800);
    }).catch(err => {
      console.error('Inline apurer fetch error', err);
      try { if (window.showToast) window.showToast('Erreur réseau lors de l\'apurement', 'danger'); else alert('Erreur réseau'); } catch(e){}
      if (btn) btn.disabled = false;
    });
  } catch(err) { console.error('submitApurerForm error', err); }
}
</script>

