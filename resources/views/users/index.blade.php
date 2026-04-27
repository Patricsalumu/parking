@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Utilisateurs</h3>
  <div class="d-flex">
    <form method="GET" action="{{ route('users.index') }}" class="me-2 d-flex">
      <input name="q" class="form-control form-control-sm" placeholder="Chercher par nom ou email" value="{{ request('q') }}">
      <button class="btn btn-sm btn-outline-secondary ms-2">Chercher</button>
    </form>
    <a href="{{ route('users.create') }}" class="btn btn-primary">Créer</a>
  </div>
</div>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Compte caisse</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($users as $u)
      <tr>
        <td>{{ $u->id }}</td>
        <td>{{ $u->name }}</td>
        <td>{{ $u->email }}</td>
        <td>{{ $u->role }}</td>
        <td>{{ $u->caisseCompte?->numero }} {{ $u->caisseCompte?->nom ? '- ' . $u->caisseCompte->nom : '' }}</td>
        <td>
          <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-warning">Modifier</a>
          <form action="{{ route('users.destroy', $u) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Supprimer</button></form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $users->appends(request()->query())->links() }}
@endsection
