@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Grand Livre - Comptes</h3>
</div>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <input type="date" name="start_date" class="form-control" value="{{ $start ?? '' }}">
  </div>
  <div class="col-md-3">
    <input type="date" name="end_date" class="form-control" value="{{ $end ?? '' }}">
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary" aria-label="Filtrer"><i class="bi bi-funnel" aria-hidden="true"></i></button>
  </div>
</form>

<table class="table table-sm table-striped">
  <thead>
    <tr>
      <th>Compte</th>
      <th>Nom</th>
      <th class="text-end">Total Débit</th>
      <th class="text-end">Total Crédit</th>
      <th class="text-end">Solde</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($comptes as $c)
      @php $d = $data[$c->id]['debit'] ?? 0; $cr = $data[$c->id]['credit'] ?? 0; $bal = $data[$c->id]['balance'] ?? 0; @endphp
      <tr>
        <td>{{ $c->numero }}</td>
        <td>{{ $c->nom }}</td>
        <td class="text-end">{{ number_format($d,2,',',' ') }}</td>
        <td class="text-end">{{ number_format($cr,2,',',' ') }}</td>
        <td class="text-end">{{ number_format($bal,2,',',' ') }}</td>
        <td><a href="{{ route('journal_comptes.grand_show', $c) }}" class="btn btn-sm btn-primary">Détails</a></td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="small-pagination">{{ $comptes->links() }}</div>

@endsection
