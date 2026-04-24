@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Entrées</h3>
  <a href="{{ route('entrees.create') }}" class="btn btn-primary">Nouvelle Entrée</a>
</div>
<form method="GET" action="{{ route('entrees.index') }}" class="mb-3">
  <div class="row g-2">
    <div class="col-md-3">
      <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher plaque / client / utilisateur">
    </div>
    <div class="col-md-2">
      <select name="client_id" class="form-select">
        <option value="">-- Client --</option>
        @foreach($clients ?? [] as $c)
          <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <select name="user_id" class="form-select">
        <option value="">-- Utilisateur --</option>
        @foreach($users ?? [] as $u)
          <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <input type="date" name="start_date" class="form-control" value="{{ request('start_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
    </div>
    <div class="col-md-2">
      <input type="date" name="end_date" class="form-control" value="{{ request('end_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
    </div>
    <div class="col-md-1 d-grid">
      <button class="btn btn-secondary">Filter</button>
    </div>
  </div>
  <div class="mt-2 d-flex gap-2">
    @php $qs = http_build_query(request()->except('page')) @endphp
    <a href="{{ url('entrees/export/csv') }}?{{ $qs }}" class="btn btn-outline-success btn-sm">Export CSV</a>
    <a href="{{ url('entrees/export/pdf') }}?{{ $qs }}" class="btn btn-outline-primary btn-sm">Export PDF</a>
    <a href="{{ route('entrees.index') }}" class="btn btn-light btn-sm">Clear</a>
  </div>
</form>
<div class="mb-2">Résultats: <span class="badge bg-info">{{ $entrees->total() }}</span></div>

<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Numero</th>
      <th>Vehicule</th>
      <th>Catégorie</th>
      <th>Client</th>
      <th>Date Entrée</th>
      <th>Date Sortie</th>
      <th>Durée</th>
      <th>Utilisateur</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($entrees as $e)
      <tr class="{{ $e->date_sortie ? 'table-danger' : '' }}">
        <td>{{ $entrees->firstItem() + $loop->index }}</td>
        <td>{{ $e->numero_formatted ?? $e->numero }}</td>
        <td>{{ $e->vehicule?->plaque }}</td>
        <td>{{ $e->categorie?->nom }}</td>
        <td>{{ $e->client?->nom }}</td>
        <td>{{ $e->date_entree ? $e->date_entree->format('Y-m-d H:i') : '' }}</td>
        <td>{{ $e->date_sortie ? $e->date_sortie->format('Y-m-d H:i') : '' }}</td>
        @php
          if ($e->date_entree) {
            $start = \Carbon\Carbon::parse($e->date_entree);
            $end = $e->date_sortie ? \Carbon\Carbon::parse($e->date_sortie) : \Carbon\Carbon::now();
            $days = $end->diffInDays($start);
            $hours = $end->diffInHours($start) % 24;
            $minutes = $end->diffInMinutes($start) % 60;
            $duration = $days . 'j ' . $hours . 'h ' . $minutes . 'm';
          } else {
            $duration = 'N/A';
          }
        @endphp
        <td>@if($e->date_sortie)<span class="text-danger">{{ $duration }}</span>@else{{ $duration }}@endif</td>
        <td>{{ $e->user?->name }}</td>
        <td>
          <a href="{{ route('entrees.edit', $e) }}" class="btn btn-sm btn-warning">Modifier</a>
          <a href="{{ route('entrees.print', $e) }}" target="_blank" class="btn btn-sm btn-primary">Imprimer</a>
          <form action="{{ route('entrees.destroy', $e) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $entrees->appends(request()->all())->links() }}
@endsection
