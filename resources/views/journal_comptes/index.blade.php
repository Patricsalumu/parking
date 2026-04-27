@extends('layouts.app')

@section('content')
<h3>Journal Comptable</h3>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <a class="btn btn-sm btn-success" href="{{ route('journal_comptes.create') }}">Nouvelle écriture</a>
  </div>
  <form method="get" action="{{ route('journal_comptes.index') }}" class="d-flex" style="gap:8px;">
    <input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Rechercher libellé, compte ou référence" />
    <input name="start_date" type="date" value="{{ request('start_date') ?? \Carbon\Carbon::today()->toDateString() }}" class="form-control form-control-sm" />
    <input name="end_date" type="date" value="{{ request('end_date') ?? \Carbon\Carbon::today()->toDateString() }}" class="form-control form-control-sm" />
    <button class="btn btn-sm btn-outline-primary">Recherche</button>
  </form>
</div>
<table class="table table-sm">
  <thead>
    <tr>
      <th>Écriture</th>
      <th>Date</th>
      <th>Libellé</th>
      <th>Compte</th>
      <th class="text-end">Débit</th>
      <th class="text-end">Crédit</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $r)
      <tr>
        <td rowspan="2" class="align-middle bg-light">#{{ $r->id }}</td>
        <td rowspan="2">{{ $r->date }}</td>
        <td rowspan="2">{{ $r->libelle }}</td>
        <td>{{ $r->compteDebit?->numero }} - {{ $r->compteDebit?->nom }}</td>
        <td class="text-end">{{ number_format($r->montant,2) }}</td>
        <td class="text-end"></td>
        <td rowspan="2">
          @php
            $isAnnule = \App\Models\JournalCompte::where('reference', 'annule_'.$r->id)->exists();
          @endphp
          @if($isAnnule)
            <button class="btn btn-sm btn-secondary" disabled>Annulé</button>
          @else
            <form method="post" action="{{ route('journal_comptes.annuler', $r) }}" onsubmit="return confirm('Confirmer annulation de l\'écriture #'+{{ $r->id }}+' ?')">
              @csrf
              <button type="submit" class="btn btn-sm btn-danger">Annuler</button>
            </form>
          @endif
        </td>
      </tr>
      <tr style="border-bottom:2px solid #dee2e6;">
        <td>{{ $r->compteCredit?->numero }} - {{ $r->compteCredit?->nom }}</td>
        <td class="text-end"></td>
        <td class="text-end">{{ number_format($r->montant,2) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
  <style>
    /* remove previous/next pagination controls entirely */
    .pagination .page-item .page-link[aria-label="Previous"],
    .pagination .page-item .page-link[aria-label="Next"] {
      display: none !important;
    }
  </style>
  {{ $rows->links() }}
</div>
@endsection

@push('scripts')
<script>
;(function(){
  // no extra scripts in index page; lookups are on create page
})();
</script>
@endpush
