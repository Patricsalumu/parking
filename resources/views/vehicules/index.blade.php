@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Véhicules</h3>
  <a href="{{ route('vehicules.create') }}" class="btn btn-primary">Create</a>
</div>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Plaque</th><th>Marque</th><th>Client</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($vehicules as $v)
      <tr>
        <td>{{ $v->id }}</td>
        <td>{{ $v->plaque }}</td>
        <td>{{ $v->marque }}</td>
        <td>{{ $v->client?->nom }}</td>
        <td>
          <a href="{{ route('vehicules.edit', $v) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('vehicules.destroy', $v) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $vehicules->links() }}
@endsection
