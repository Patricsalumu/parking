@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Véhicules</h3>
  <div class="d-flex align-items-center">
    <form method="GET" class="d-flex me-2">
      <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Rechercher plaque / client">
      <button class="btn btn-outline-secondary btn-sm ms-2">Rechercher</button>
    </form>
    <a href="{{ route('vehicules.export.csv') }}?{{ http_build_query(request()->only('q')) }}" class="btn btn-outline-success btn-sm me-2">Export CSV</a>
    <a href="{{ route('vehicules.export.pdf') }}?{{ http_build_query(request()->only('q')) }}" class="btn btn-outline-primary btn-sm me-2">Export PDF</a>
    <a href="{{ route('vehicules.create') }}" class="btn btn-primary">Nouveau Véhicule</a>
  </div>
</div>
<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Plaque</th>
      <th>Client</th>
      <th># Entrées</th>
      <th>Total facturé</th>
      <th>Total payé</th>
      <th>Reste</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($vehicules as $v)
    @php
      $totalBilled = $v->total_billed ?? null;
      $totalPaid = $v->total_paid ?? null;
      if ($totalBilled === null) {
        $entries = $v->entrees ?? collect();
        $totalBilled = 0; $totalPaid = 0;
        foreach($entries as $en){ if($en->facturation){ $f=$en->facturation; $totalBilled += $f->montant_total ?? 0; $totalPaid += $f->montant_paye ?? 0; }}
      }
      $remaining = $totalBilled - $totalPaid;
    @endphp
    <tr>
      <td>{{ $vehicules->firstItem() + $loop->index }}</td>
      <td>{{ $v->plaque }}</td>
      <td>{{ $v->client?->nom }}</td>
      <td>{{ $v->entrees_count ?? ($v->entrees ? $v->entrees->count() : 0) }}</td>
      <td>{{ number_format($totalBilled,2) }}</td>
      <td>{{ number_format($totalPaid ?? 0,2) }}</td>
      <td>{{ number_format($remaining,2) }}</td>
      <td>
        <a href="{{ route('vehicules.show', $v) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
        <a href="{{ route('vehicules.edit', $v) }}" class="btn btn-sm btn-warning">Modifier</a>
        <form action="{{ route('vehicules.destroy', $v) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="small-pagination">{{ $vehicules->links() }}</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const container = document.querySelector('.small-pagination');
  if (!container) return;
  // remove any inline SVG icons and bootstrap-icon <i> elements inside pagination links
  container.querySelectorAll('svg, .bi').forEach(el => el.remove());
});
</script>
@endpush

@endsection
