@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card mt-5">
      <div class="card-body">
        <h4 class="card-title mb-3">Login</h4>
        <form method="POST" action="{{ route('login.post') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control">
          </div>
          <button class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
