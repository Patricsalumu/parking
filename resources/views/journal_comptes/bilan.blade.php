@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Bilan (par classes)</h3>
</div>
<div class="mb-2">Période: {{ $start }} → {{ $end }}</div>
<div class="row">
  <div class="col-md-6">
    <h5>Actif (groupé par sous-classe)</h5>
    @foreach($assets_groups as $group)
      <div class="mb-2">
        <strong>Classe {{ $group['label'] }}</strong>
        <div class="text-end">Total: {{ number_format($group['total'],2,',',' ') }}</div>
        <table class="table table-sm mt-1 mb-3">
          <thead><tr><th>Compte</th><th>Nom</th><th class="text-end">Montant</th></tr></thead>
          <tbody>
            @foreach($group['comptes'] as $a)
              <tr>
                <td>{{ $a['compte']->numero }}</td>
                <td>{{ $a['compte']->nom }}</td>
                <td class="text-end">{{ number_format($a['value'],2,',',' ') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endforeach
  </div>
  <div class="col-md-6">
    <h5>Passif (groupé par sous-classe)</h5>
    @foreach($passifs_groups as $group)
      <div class="mb-2">
        <strong>Classe {{ $group['label'] }}</strong>
        <div class="text-end">Total: {{ number_format($group['total'],2,',',' ') }}</div>
        <table class="table table-sm mt-1 mb-3">
          <thead><tr><th>Compte</th><th>Nom</th><th class="text-end">Montant</th></tr></thead>
          <tbody>
            @foreach($group['comptes'] as $p)
              <tr>
                <td>{{ $p['compte']->numero }}</td>
                <td>{{ $p['compte']->nom }}</td>
                <td class="text-end">{{ number_format($p['value'],2,',',' ') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endforeach
  </div>
</div>
  <div class="mt-3">
    <div><strong>Total Actif:</strong> {{ number_format($assets_total ?? 0,2,',',' ') }}</div>
    <div><strong>Total Passif:</strong> {{ number_format($passifs_total ?? 0,2,',',' ') }}</div>
  </div>
@endsection
