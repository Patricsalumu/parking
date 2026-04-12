@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Facturations</h3>
  <div></div>
</div>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Entrée</th><th>Vehicule</th><th>Total</th><th>Paye</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($facturations as $f)
      <tr>
        <td>{{ $f->id }}</td>
        <td>{{ $f->entree_id }}</td>
        <td>{{ $f->entree->vehicule?->plaque }}</td>
        <td>{{ $f->montant_total }}</td>
        <td>{{ $f->montant_paye }}</td>
        <td><a href="{{ route('facturations.show', $f) }}" class="btn btn-sm btn-primary">View</a></td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $facturations->links() }}
@endsection
