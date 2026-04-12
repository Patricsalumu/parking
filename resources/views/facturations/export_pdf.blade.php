@extends('layouts.app')

@section('content')
  <h3>Export Facturations</h3>
  <table class="table table-sm">
    <thead><tr><th>ID</th><th>Date</th><th>Entree</th><th>Plaque</th><th>Client</th><th>Catégorie</th><th>Total</th><th>Paye</th><th>Reste</th><th>Utilisateur</th></tr></thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i') : '' }}</td>
          <td>{{ $r->entree_id }}</td>
          <td>{{ $r->entree?->vehicule?->plaque }}</td>
          <td>{{ $r->entree?->client?->nom }}</td>
          <td>{{ $r->categorie?->nom }}</td>
          <td>{{ number_format($r->montant_total,2) }}</td>
          <td>{{ number_format($r->montant_paye,2) }}</td>
          <td>{{ number_format(($r->montant_total - ($r->montant_paye ?? 0)),2) }}</td>
          <td>{{ $r->user?->name }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection
