@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Comptes</h3>
  <a href="{{ route('comptes.create') }}" class="btn btn-primary">Créer</a>
</div>

<table class="table table-sm">
  <thead>
    <tr>
      <th>Numéro</th>
      <th>Intitulé</th>
      <th>Classe</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @foreach($comptes as $c)
      <tr>
        <td>{{ $c->numero }}</td>
        <td>{{ $c->nom }}</td>
        <td>{{ $c->classe?->numero }} - {{ $c->classe?->nom }}</td>
        <td>
          <a href="{{ route('comptes.edit', $c) }}" class="btn btn-sm btn-secondary">Modifier</a>
          <form method="POST" action="{{ route('comptes.destroy', $c) }}" style="display:inline">@csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

{{ $comptes->links() }}

@endsection
