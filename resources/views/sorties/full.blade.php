@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="card">
    <div class="card-body">
      @include('sorties.show')
    </div>
  </div>
</div>
@endsection
