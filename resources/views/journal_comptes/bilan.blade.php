@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Bilan (par classes)</h3>
</div>
<div class="mb-2">Période: {{ $start }} → {{ $end }}</div>
<div class="row">
  <div class="col-md-6">
    <h5>Actif (classes 2,3,4,5)</h5>
    <table class="table table-sm">
      <thead><tr><th>Classe</th><th class="text-end">Total</th></tr></thead>
      <tbody>
        @foreach($class_totals as $num => $sum)
          @if(in_array($num, ['2','3','4','5']))
            <tr><td>{{ $num }}</td><td class="text-end">{{ number_format($sum,2,',',' ') }}</td></tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="col-md-6">
    <h5>Passif (classe 1)</h5>
    <table class="table table-sm">
      <thead><tr><th>Classe</th><th class="text-end">Total</th></tr></thead>
      <tbody>
        @foreach($class_totals as $num => $sum)
          @if(in_array($num, ['1']))
            <tr><td>{{ $num }}</td><td class="text-end">{{ number_format($sum,2,',',' ') }}</td></tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3">
  <div><strong>Total Actif:</strong> {{ number_format($assets_total ?? 0,2,',',' ') }}</div>
  <div><strong>Total Passif:</strong> {{ number_format($passifs_total ?? 0,2,',',' ') }}</div>
  <div><strong>Total Charges:</strong> {{ number_format($total_charges ?? 0,2,',',' ') }}</div>
  <div><strong>Total Produits:</strong> {{ number_format($total_produits ?? 0,2,',',' ') }}</div>
  <div><strong>Résultat Net:</strong> {{ number_format($resultat ?? 0,2,',',' ') }}</div>
</div>
@endsection
