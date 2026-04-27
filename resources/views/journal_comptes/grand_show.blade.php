@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Grand Livre - {{ $compte->numero }} - {{ $compte->nom }}</h3>
  <div>
    <a href="{{ route('journal_comptes.grand_index') }}" class="btn btn-light">Retour</a>
  </div>
</div>

<div class="mb-2">Période: {{ $start }} → {{ $end }}</div>
<div class="mb-2">Total Débit: {{ number_format($total_debit,2,',',' ') }} | Total Crédit: {{ number_format($total_credit,2,',',' ') }} | Solde: {{ number_format($balance,2,',',' ') }}</div>

<table class="table table-sm table-striped">
  <thead>
    <tr>
      <th>Date</th>
      <th>Libellé</th>
      <th>Compte Débit</th>
      <th>Compte Crédit</th>
      <th class="text-end">Montant</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $r)
      <tr>
        <td>{{ $r->date }}</td>
        <td>{{ $r->libelle }}</td>
        <td>{{ $r->compteDebit?->numero }} - {{ $r->compteDebit?->nom }}</td>
        <td>{{ $r->compteCredit?->numero }} - {{ $r->compteCredit?->nom }}</td>
        <td class="text-end">{{ number_format($r->montant,2,',',' ') }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="small-pagination">{{ $rows->links() }}</div>

@endsection
