@extends('layouts.app')

@section('content')
<h3>Facturation #{{ $facturation->id }}</h3>
<div class="card p-3">
  <p><strong>Entrée:</strong> {{ $facturation->entree_id }}</p>
  <p><strong>Vehicule:</strong> {{ $facturation->entree->vehicule?->plaque }}</p>
  <p><strong>Durée (jours):</strong> {{ $facturation->duree }}</p>
  <p><strong>Total:</strong> {{ $facturation->montant_total }}</p>
  <p><strong>Montant payé:</strong> {{ $facturation->montant_paye }}</p>
  <p><strong>Balance:</strong> {{ $facturation->montant_total - $facturation->montant_paye }}</p>
</div>
<div class="mt-2 d-flex gap-2">
  <button id="btnPayShow" class="btn btn-success">Payer</button>
  <a href="{{ route('facturations.print', $facturation) }}" target="_blank" class="btn btn-primary">Imprimer</a>
</div>

<!-- Payment Modal (show) -->
<div class="modal fade" id="payModalShow" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="payFormShow" method="POST" action="{{ route('paiements.store') }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Enregistrer Paiement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="facturation_id" id="modal_show_facturation_id" value="{{ $facturation->id }}">
          <div class="mb-3"><label>Montant</label><input id="modal_show_montant" name="montant" class="form-control" required></div>
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
  const payModal = new bootstrap.Modal(document.getElementById('payModalShow'));
  const btn = document.getElementById('btnPayShow');
  btn.addEventListener('click', function(){
    const balance = parseFloat('{{ $facturation->montant_total - $facturation->montant_paye }}') || 0;
    document.getElementById('modal_show_montant').value = balance.toFixed(2);
    payModal.show();
  });
});
</script>
@endpush
@endsection
