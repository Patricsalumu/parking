@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Balances</h3>
</div>
<div class="mb-2">Période: {{ $start }} → {{ $end }}</div>
<table class="table table-sm table-striped">
  <thead>
    <tr>
      <th>Compte</th>
      <th>Nom</th>
      <th class="text-end">Débit</th>
      <th class="text-end">Crédit</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $r)
      <tr>
        <td>{{ $r['compte']->numero }}</td>
        <td>{{ $r['compte']->nom }}</td>
        <td class="text-end">{{ number_format($r['debit'],2,',',' ') }}</td>
        <td class="text-end">{{ number_format($r['credit'],2,',',' ') }}</td>
      </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <th colspan="2">Total</th>
      <th class="text-end">{{ number_format($total_debit,2,',',' ') }}</th>
      <th class="text-end">{{ number_format($total_credit,2,',',' ') }}</th>
    </tr>
  </tfoot>
</table>
@endsection
