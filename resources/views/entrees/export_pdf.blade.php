@extends('layouts.app')

@section('content')
  <h3>Export Entrées</h3>
  <table class="table table-sm">
    <thead><tr><th>ID</th><th>Date Entree</th><th>Plaque</th><th>Compagnie</th><th>Client</th><th>Utilisateur</th></th></tr></thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->date_entree ? $r->date_entree->format('Y-m-d H:i') : '' }}</td>
          <td>{{ $r->vehicule?->plaque }}</td>
          <td>{{ $r->vehicule?->compagnie }}</td>
          <td>{{ $r->client?->nom }}</td>
          <td>{{ $r->user?->name }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection
