@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Classes</h3>
  <a href="{{ route('classes.create') }}" class="btn btn-primary">Create</a>
</div>
<table class="table">
  <thead><tr><th>ID</th><th>Nom</th><th>Numero</th><th>Type</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($classes as $c)
      <tr>
        <td>{{ $c->id }}</td>
        <td>{{ $c->nom }}</td>
        <td>{{ $c->numero }}</td>
        <td>{{ $c->type }}</td>
        <td>
          <a href="{{ route('classes.edit', $c) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('classes.destroy', $c) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $classes->links() }}
@endsection
