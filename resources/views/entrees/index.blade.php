@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Entrées</h3>
  <a href="{{ route('entrees.create') }}" class="btn btn-primary">New Entry</a>
</div>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Vehicule</th><th>Client</th><th>Date Entree</th><th>Date Sortie</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($entrees as $e)
      <tr>
        <td>{{ $e->id }}</td>
        <td>{{ $e->vehicule?->plaque }}</td>
        <td>{{ $e->client?->nom }}</td>
        <td>{{ $e->date_entree }}</td>
        <td>{{ $e->date_sortie }}</td>
        <td>
          <a href="{{ route('entrees.edit', $e) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('entrees.destroy', $e) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $entrees->links() }}
@endsection
