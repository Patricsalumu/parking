@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Catégories</h3>
  <a href="{{ route('categories.create') }}" class="btn btn-primary">Create</a>
</div>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Nom</th><th>Prix /24h</th><th>Compte produit</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($categories as $c)
      <tr>
        <td>{{ $c->id }}</td>
        <td>{{ $c->nom }}</td>
        <td>{{ $c->prix_par_24h }}</td>
        <td>{{ $c->compteProduit?->numero }} {{ $c->compteProduit?->nom ? '- ' . $c->compteProduit->nom : '' }}</td>
        <td>
          <a href="{{ route('categories.edit', $c) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('categories.destroy', $c) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $categories->links() }}
@endsection
