@extends('layouts.app')

@section('content')
<div class="mb-3">
  <div class="row g-2 align-items-center">
    <div class="col-12 col-md-8">
      <div class="d-flex">
        <form method="GET" class="d-flex w-100">
          <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm me-2" placeholder="Rechercher nom / email / téléphone">
          <button class="btn btn-outline-secondary btn-sm" aria-label="Rechercher"><i class="bi bi-search" aria-hidden="true"></i></button>
        </form>
      </div>
    </div>
    <div class="col-12 col-md-4 text-md-end">
      <a href="{{ route('clients.export.csv') }}?{{ http_build_query(request()->only('q')) }}" class="btn btn-outline-success btn-sm me-2">Export CSV</a>
      <a href="{{ route('clients.export.pdf') }}?{{ http_build_query(request()->only('q')) }}" class="btn btn-outline-primary btn-sm me-2">Export PDF</a>
      <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">Nouveau</a>
    </div>
  </div>
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Nom</th>
      <th>Contact</th>
      <th># Entrées</th>
      <th>Total facturé</th>
      <th>Total payé</th>
      <th>Total réduction</th>
      <th>Reste</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($clients as $c)
    @php
      // use DB-side aggregates if available (selected as columns), otherwise fallback to relation calc
      $totalBilled = $c->total_billed ?? null;
      $totalPaid = $c->total_paid ?? null;
      $totalReduction = $c->total_reduction ?? null;
      if ($totalBilled === null) {
        $entries = $c->entrees ?? collect();
        $totalBilled = 0; $totalPaid = 0; $totalReduction = 0;
        foreach($entries as $en) {
          if ($en->facturation) {
            $f = $en->facturation;
            $totalBilled += $f->montant_total ?? 0;
            $totalPaid += $f->montant_paye ?? 0;
            $totalReduction += $f->reduction ?? 0;
          }
        }
      }
      $remaining = $totalBilled - $totalPaid;
    @endphp
    <tr>
      <td>{{ $clients->firstItem() + $loop->index }}</td>
      <td>{{ $c->nom }}</td>
      <td>{{ $c->telephone }}<br>{{ $c->email }}</td>
      <td>{{ $c->entrees_count ?? ($c->entrees ? $c->entrees->count() : 0) }}</td>
      <td>{{ number_format($totalBilled,2) }}</td>
      <td>{{ number_format($totalPaid ?? 0,2) }}</td>
      <td>{{ number_format($totalReduction ?? 0,2) }}</td>
      <td>{{ number_format($remaining,2) }}</td>
      <td>
        <a href="{{ route('clients.show', $c) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
        <a href="{{ route('clients.edit', $c) }}" class="btn btn-sm btn-warning">Modifier</a>
        <form action="{{ route('clients.destroy', $c) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="small-pagination">{{ $clients->links() }}</div>

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
