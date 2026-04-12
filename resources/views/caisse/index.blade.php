
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Caisse - Grand Livre</h3>
  <div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#sortieModal">Enregistrer sortie</button>
  </div>
</div>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <input type="date" name="start_date" class="form-control" value="{{ $start ?? '' }}" placeholder="Date début">
  </div>
  <div class="col-md-3">
    <input type="date" name="end_date" class="form-control" value="{{ $end ?? '' }}" placeholder="Date fin">
  </div>
  <div class="col-md-3">
    <select name="compte_id" class="form-control">
      <option value="">-- Tous les comptes --</option>
      @foreach($comptes as $c)
        <option value="{{ $c->id }}" {{ (string)($compte_id ?? '') === (string)$c->id ? 'selected' : '' }}>{{ $c->numero }} - {{ $c->nom }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-2">
    <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Recherche libellé">
  </div>
  <div class="col-md-1">
    <button class="btn btn-primary w-100">Filtrer</button>
  </div>
</form>

<div class="mb-3">
  <strong>Total Entrées:</strong> {{ number_format($total_entrees ?? 0,2,',',' ') }}
  &nbsp; | &nbsp;
  <strong>Total Sorties:</strong> {{ number_format($total_sorties ?? 0,2,',',' ') }}
  &nbsp; | &nbsp;
  <strong>Solde:</strong> {{ number_format($balance ?? 0,2,',',' ') }}
</div>

<table class="table table-sm table-striped">
  <thead>
    <tr>
      <th>Date</th>
      <th>Libellé</th>
      <th>Compte Débit</th>
      <th>Compte Crédit</th>
      <th class="text-end">Entrées</th>
      <th class="text-end">Sorties</th>
    </tr>
  </thead>
  <tbody>
    @foreach($entries as $e)
      <tr>
        <td>{{ $e->date }}</td>
        <td>{{ $e->libelle }}</td>
        <td>{{ $e->compteDebit? $e->compteDebit->numero . ' - ' . $e->compteDebit->nom : '' }}</td>
        <td>{{ $e->compteCredit? $e->compteCredit->numero . ' - ' . $e->compteCredit->nom : '' }}</td>
        @if($compte_id)
          <td class="text-end">@if($e->compte_debit_id == $compte_id) {{ number_format($e->montant,2,',',' ') }} @endif</td>
          <td class="text-end">@if($e->compte_credit_id == $compte_id) {{ number_format($e->montant,2,',',' ') }} @endif</td>
        @else
          <td class="text-end">@if($e->compte_debit_id) {{ number_format($e->montant,2,',',' ') }} @endif</td>
          <td class="text-end">@if($e->compte_credit_id) {{ number_format($e->montant,2,',',' ') }} @endif</td>
        @endif
      </tr>
    @endforeach
  </tbody>
</table>

{{ $entries->links() }}

@endsection

<!-- Sortie modal -->
<div class="modal fade" id="sortieModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('caisse.sortie') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Enregistrer une sortie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Compte à débiter</label>
            <select name="compte_debit_id" class="form-control">
              @foreach($comptes as $c)
                <option value="{{ $c->id }}">{{ $c->numero }} - {{ $c->nom }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label>Montant</label>
            <input type="number" step="0.01" min="0" name="montant" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Libellé</label>
            <input type="text" name="libelle" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button class="btn btn-primary">Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>
