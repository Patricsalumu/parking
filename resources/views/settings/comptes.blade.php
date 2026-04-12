@extends('layouts.app')

@section('content')
<h3>Comptes</h3>
<a href="{{ route('comptes.create') }}" class="btn btn-primary mb-3">Create Compte</a>
<table class="table">
  <thead><tr><th>ID</th><th>Nom</th><th>Numero</th><th>Classe</th></tr></thead>
  <tbody>
    @foreach($comptes as $c)
      <tr><td>{{ $c->id }}</td><td>{{ $c->nom }}</td><td>{{ $c->numero }}</td><td>{{ $c->classe?->nom }}</td></tr>
    @endforeach
  </tbody>
</table>
{{ $comptes->links() }}
@endsection
