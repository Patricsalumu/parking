@extends('layouts.app')

<style>
  body.login-bg { 
    background-image: url("{{ asset('storage/logos/backgound1.jpeg') }}");
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
  }
  /* make the card frame/background semi-transparent on login (50% opacity) */
  body.login-bg .card {
    background-color: rgba(255,255,255,0.5) !important;
    border: none !important;
    box-shadow: none !important;
  }
  /* subtle dark overlay to improve contrast */
  body.login-bg::before {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    z-index: 0;
    pointer-events: none;
  }
  /* ensure card sits above the overlay */
  body.login-bg .card { position: relative; z-index: 1; }
</style>

@section('content')
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card mt-5">
      <div class="card-body">
        <h4 class="card-title mb-3">Connexion</h4>
        <form method="POST" action="{{ route('login.post') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Email ou nom utilisateur</label>
            <input type="text" name="login" class="form-control" value="{{ old('login') }}" placeholder="email ou nom utilisateur">
            @if($errors->has('login')) <div class="text-danger small mt-1">{{ $errors->first('login') }}</div> @endif
          </div>
          <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control">
            @if($errors->has('password')) <div class="text-danger small mt-1">{{ $errors->first('password') }}</div> @endif
          </div>
          <button class="btn btn-primary">Se connecter</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  try { document.body.classList.add('login-bg'); } catch(e){}
</script>
@endpush
