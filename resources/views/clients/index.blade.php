@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Clients</h3>
  <a href="{{ route('clients.create') }}" class="btn btn-primary">Create</a>
</div>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Nom</th><th>Telephone</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($clients as $c)
      <tr>
        <td>{{ $c->id }}</td>
        <td>{{ $c->nom }}</td>
        <td>{{ $c->telephone }}</td>
        <td>
          <a href="{{ route('clients.edit', $c) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('clients.destroy', $c) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $clients->links() }}
@endsection
