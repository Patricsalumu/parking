@extends('layouts.app')
@section('content')
<div style="max-width:900px;margin:0 auto;">
  <h3>Véhicules Report</h3>
  <table class="table table-sm">
    <thead><tr><th>ID</th><th>Plaque</th><th>Client</th><th>#Entrées</th><th>Total facturé</th><th>Total payé</th><th>Reste</th></tr></thead>
    <tbody>
      @foreach($rows as $r)
        @php $totalBilled = $r->total_billed ?? 0; $totalPaid = $r->total_paid ?? 0; $remaining = $totalBilled - $totalPaid; @endphp
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->plaque }}</td>
          <td>{{ $r->client?->nom }}</td>
          <td>{{ $r->entrees_count ?? 0 }}</td>
          <td>{{ number_format($totalBilled,2) }}</td>
          <td>{{ number_format($totalPaid,2) }}</td>
          <td>{{ number_format($remaining,2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
