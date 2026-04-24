<div class="modal-body">
  <div class="row">
    <div class="col-md-6">
      <div class="mb-2"><strong>Client:</strong> {{ $entree->client?->nom ?? 'N/C' }}</div>
      <div class="mb-2"><strong>Date entrée:</strong> {{ $entree->date_entree ? \Carbon\Carbon::parse($entree->date_entree)->format('Y-m-d H:i') : '' }}</div>
      <div class="mb-2"><strong>Utilisateur entrée:</strong> {{ $entree->user?->name }}</div>
      <div class="mb-2"><strong>Date sortie:</strong> {{ $entree->date_sortie ? \Carbon\Carbon::parse($entree->date_sortie)->format('Y-m-d H:i') : '-' }}</div>
      <div class="mb-2"><strong>Utilisateur sortie:</strong> {{ $entree->sortieUser?->name ?? '-' }}</div>
      <hr>
      <h6>Facturation</h6>
      @if($fact)
        <div><strong>Facture #</strong> {{ $fact->id }}</div>
        <div><strong>Catégorie:</strong> {{ $fact->categorie?->nom ?? 'N/C' }}</div>
        <div><strong>Duree (jours):</strong> {{ $fact->duree ?? $entree->durationInDays() ?? 'N/A' }}</div>
        <div><strong>Total:</strong> {{ number_format($fact->montant_total ?? 0,2) }}</div>
        <div><strong>Payé:</strong> {{ number_format($fact->montant_paye ?? 0,2) }}</div>
        <div><strong>Reste:</strong> {{ number_format( ($fact->montant_total - ($fact->montant_paye ?? 0)) ,2) }}</div>
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
    @if($canExit)
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmSortieModal">Confirmer Sortie</button>
    @else
      <button class="btn btn-warning" disabled>Sortie non permise (facture non apurée)</button>
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

