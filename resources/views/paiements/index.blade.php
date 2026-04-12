@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Paiements</h3>
  <div></div>
</div>
<table class="table">
  <thead><tr><th>ID</th><th>Facturation</th><th>Montant</th><th>Date</th></tr></thead>
  <tbody>
    @foreach($paiements as $p)
      <tr>
        <td>{{ $p->id }}</td>
        <td>{{ $p->facturation_id }}</td>
        <td>{{ $p->montant }}</td>
        <td>{{ $p->date_paiement }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
{{ $paiements->links() }}
@endsection
