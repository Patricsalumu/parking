@extends('layouts.app')

@section('content')
<h3>Classes</h3>
<a href="{{ route('classes.create') }}" class="btn btn-primary mb-3">Create Classe</a>
<table class="table">
  <thead><tr><th>ID</th><th>Nom</th><th>Numero</th><th>Type</th></tr></thead>
  <tbody>
    @foreach($classes as $c)
      <tr><td>{{ $c->id }}</td><td>{{ $c->nom }}</td><td>{{ $c->numero }}</td><td>{{ $c->type }}</td></tr>
    @endforeach
  </tbody>
</table>
{{ $classes->links() }}
@endsection
