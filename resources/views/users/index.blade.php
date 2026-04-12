@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Users</h3>
  <a href="#" class="btn btn-primary">Create</a>
</div>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($users as $u)
      <tr>
        <td>{{ $u->id }}</td>
        <td>{{ $u->name }}</td>
        <td>{{ $u->email }}</td>
        <td>{{ $u->role }}</td>
        <td>
          <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('users.destroy', $u) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $users->links() }}
@endsection
