@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Stocks Physique</h3>
  <span class="badge bg-info fs-6 align-self-center">{{ $entrees->total() }} véhicule(s) en stock</span>
</div>

<form method="GET" action="{{ route('stocks_physique.index') }}" class="mb-3">
  <div class="row g-2">
    <div class="col-md-4">
      <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher plaque ou client..." autofocus>
    </div>
    <div class="col-auto">
      <button class="btn btn-secondary" type="submit"><i class="bi bi-search"></i></button>
    </div>
    @if(request('q'))
      <div class="col-auto">
        <a href="{{ route('stocks_physique.index') }}" class="btn btn-light">Effacer</a>
      </div>
    @endif
  </div>
</form>

<table class="table table-striped table-bordered table-sm align-middle">
  <thead class="table-dark">
    <tr>
      <th>#</th>
      <th>N° Entrée</th>
      <th>Plaque</th>
      <th>Client</th>
      <th>Date Entrée</th>
      <th>Durée</th>
      <th>Utilisateur</th>
    </tr>
  </thead>
  <tbody>
    @forelse($entrees as $e)
      @php
        $start = $e->date_entree ? \Carbon\Carbon::parse($e->date_entree) : null;
        if ($start) {
            $end = \Carbon\Carbon::now();
            $days    = $end->diffInDays($start);
            $hours   = $end->diffInHours($start) % 24;
            $minutes = $end->diffInMinutes($start) % 60;
            $duration = $days . 'j ' . $hours . 'h ' . $minutes . 'm';
        } else {
            $duration = 'N/A';
        }
      @endphp
      <tr>
        <td>{{ $entrees->firstItem() + $loop->index }}</td>
        <td>{{ $e->numero_formatted ?? $e->numero }}</td>
        <td>{{ $e->vehicule?->plaque }}</td>
        <td>{{ $e->client?->nom }}</td>
        <td>{{ $e->date_entree ? format_dt($e->date_entree) : '' }}</td>
        <td>{{ $duration }}</td>
        <td>{{ $e->user?->name }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="7" class="text-center text-muted">Aucun véhicule en stock actuellement.</td>
      </tr>
    @endforelse
  </tbody>
</table>

<div class="small-pagination">{{ $entrees->links() }}</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const container = document.querySelector('.small-pagination');
  if (!container) return;
  // Remove previous/next arrow icons, keep only page numbers
  container.querySelectorAll('.page-item').forEach(function (item) {
    const link = item.querySelector('.page-link');
    if (!link) return;
    // detect prev/next by rel attribute or aria-label
    const rel = link.getAttribute('rel');
    const aria = (link.getAttribute('aria-label') || '').toLowerCase();
    if (rel === 'prev' || rel === 'next' || aria.includes('previous') || aria.includes('next')) {
      item.remove();
    }
  });
});
</script>
@endpush

@endsection
