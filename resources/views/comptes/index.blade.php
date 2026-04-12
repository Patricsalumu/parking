@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Comptes</h3>
  <a href="{{ route('comptes.create') }}" class="btn btn-primary">Create</a>
</div>
<table class="table">
  <thead><tr><th>ID</th><th>Nom</th><th>Numero</th><th>Classe</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($comptes as $c)
      <tr>
        <td>{{ $c->id }}</td>
        <td>{{ $c->nom }}</td>
        <td>{{ $c->numero }}</td>
        <td>{{ $c->classe?->nom }}</td>
        <td>
          <a href="{{ route('comptes.edit', $c) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('comptes.destroy', $c) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $comptes->links() }}
@endsection
