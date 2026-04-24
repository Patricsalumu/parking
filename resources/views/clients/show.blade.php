@extends('layouts.app')

@section('content')
<div class="mb-3">
  <h3>Client: {{ $client->nom }}</h3>
  <div>{{ $client->telephone }} — {{ $client->email }}</div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card p-3 mb-3">
      <h6>Résumé</h6>
      @php
        $entries = $client->entrees ?? collect();
        $totalBilled = 0; $totalPaid = 0; $totalReduction = 0; $countEntries = $entries->count();
        foreach($entries as $en){ if($en->facturation){ $f=$en->facturation; $totalBilled += $f->montant_total ?? 0; $totalPaid += $f->montant_paye ?? 0; $totalReduction += $f->reduction ?? 0; }}
        $remaining = $totalBilled - $totalPaid;
      @endphp
      <p># Entrées: <strong>{{ $countEntries }}</strong></p>
      <p>Total facturé: <strong>{{ number_format($totalBilled,2) }}</strong></p>
      <p>Total payé: <strong>{{ number_format($totalPaid,2) }}</strong></p>
      <p>Total réduction: <strong>{{ number_format($totalReduction,2) }}</strong></p>
      <p>Reste: <strong>{{ number_format($remaining,2) }}</strong></p>
    </div>

    <div class="card p-3">
      <h6>Véhicules</h6>
      <ul>
        @foreach($client->vehicules as $v)
          <li><a href="{{ route('vehicules.show', $v) }}">{{ $v->plaque }}</a> — {{ $v->marque ?? '-' }}</li>
        @endforeach
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 mb-3">
      <h6>Entrées</h6>
      <table class="table table-sm">
        <thead><tr><th>#</th><th>Numero</th><th>Plaque</th><th>Entrée</th><th>Sortie</th><th>Facture</th><th>Total</th><th>Payé</th></tr></thead>
        <tbody>
          @foreach($client->entrees as $en)
            <tr class="{{ $en->date_sortie ? 'table-danger' : '' }}">
              <td>{{ $en->id }}</td>
              <td>{{ $en->numero_formatted ?? $en->numero }}</td>
              <td>{{ $en->vehicule?->plaque }}</td>
              <td>{{ $en->date_entree ? \Carbon\Carbon::parse($en->date_entree)->format('Y-m-d H:i') : '' }}</td>
              <td>{{ $en->date_sortie ? \Carbon\Carbon::parse($en->date_sortie)->format('Y-m-d H:i') : '' }}</td>
              <td>@if($en->facturation) #{{ $en->facturation->numero_formatted ?? $en->facturation->numero ?? $en->facturation->id }} @else - @endif</td>
              <td>@if($en->facturation) {{ number_format($en->facturation->montant_total,2) }} @else - @endif</td>
              <td>@if($en->facturation) {{ number_format($en->facturation->montant_paye ?? 0,2) }} @else - @endif</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="card p-3">
      <h6>Facturations</h6>
      <table class="table table-sm">
        <thead><tr><th>#</th><th>Numero</th><th>Entrée</th><th>Plaque</th><th>Duree</th><th>Total</th><th>Payé</th><th>Reste</th></tr></thead>
        <tbody>
          @foreach($facturations as $f)
            <tr>
              <td>{{ $f->id }}</td>
              <td>{{ $f->numero_formatted ?? $f->numero ?? $f->id }}</td>
              <td>{{ $f->entree_id }}</td>
              <td>{{ $f->entree?->vehicule?->plaque }}</td>
              <td>{{ $f->duree }}</td>
              <td>{{ number_format($f->montant_total,2) }}</td>
              <td>{{ number_format($f->montant_paye ?? 0,2) }}</td>
              <td>{{ number_format(($f->montant_total - ($f->montant_paye ?? 0)),2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection
