@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Compte de Résultat</h3>
</div>
<div class="mb-2">Période: {{ $start }} → {{ $end }}</div>
<div class="row">
  <div class="col-md-6">
    <h5>Charges</h5>
    <table class="table table-sm">
      <thead><tr><th>Compte</th><th class="text-end">Montant</th></tr></thead>
      <tbody>
        @foreach($charges_data as $c)
          <tr><td>{{ $c['compte']->numero }} - {{ $c['compte']->nom }}</td><td class="text-end">{{ number_format($c['value'],2,',',' ') }}</td></tr>
        @endforeach
      </tbody>
      <tfoot><tr><th>Total Charges</th><th class="text-end">{{ number_format($total_charges,2,',',' ') }}</th></tr></tfoot>
    </table>
  </div>
  <div class="col-md-6">
    <h5>Produits</h5>
    <table class="table table-sm">
      <thead><tr><th>Compte</th><th class="text-end">Montant</th></tr></thead>
      <tbody>
        @foreach($produits_data as $c)
          <tr><td>{{ $c['compte']->numero }} - {{ $c['compte']->nom }}</td><td class="text-end">{{ number_format($c['value'],2,',',' ') }}</td></tr>
        @endforeach
      </tbody>
      <tfoot><tr><th>Total Produits</th><th class="text-end">{{ number_format($total_produits,2,',',' ') }}</th></tr></tfoot>
    </table>
  </div>
</div>
<div class="mt-3"><strong>Résultat Net:</strong> {{ number_format($resultat,2,',',' ') }}</div>
@endsection
