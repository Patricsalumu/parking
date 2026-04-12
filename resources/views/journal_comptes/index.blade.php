@extends('layouts.app')

@section('content')
<h3>Journal Comptable</h3>
<table class="table table-sm">
  <thead><tr><th>Date</th><th>Libellé</th><th>Montant</th><th>Débit</th><th>Crédit</th><th></th></tr></thead>
  <tbody>
    @foreach($rows as $r)
      <tr>
        <td>{{ $r->date }}</td>
        <td>{{ $r->libelle }}</td>
        <td>{{ number_format($r->montant,2) }}</td>
        <td>{{ $r->compteDebit?->numero }} - {{ $r->compteDebit?->nom }}</td>
        <td>{{ $r->compteCredit?->numero }} - {{ $r->compteCredit?->nom }}</td>
        <td><a href="{{ route('journal_comptes.show', $r) }}" class="btn btn-sm btn-primary">Voir</a></td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $rows->links() }}
@endsection
