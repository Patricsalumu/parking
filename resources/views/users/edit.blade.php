@extends('layouts.app')

@section('content')
<a href="{{ route('users.index') }}" class="btn btn-light btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Retour</a>
<h3>Modifier Utilisateur</h3>
<form method="POST" action="{{ route('users.update', $user) }}">
  @csrf @method('PUT')
  <div class="mb-3"><label>Nom</label><input name="name" value="{{ old('name', $user->name) }}" class="form-control"></div>
  <div class="mb-3"><label>Email</label><input name="email" value="{{ old('email', $user->email) }}" class="form-control"></div>
  <div class="mb-3"><label>Mot de passe (laisser vide pour conserver)</label><input name="password" type="password" class="form-control"></div>
  <div class="mb-3"><label>Confirmer le mot de passe</label><input name="password_confirmation" type="password" class="form-control"></div>
  <div class="mb-3"><label>Rôle</label><select name="role" class="form-control"><option value="user" {{ old('role', $user->role)=='user'?'selected':'' }}>user</option><option value="superadmin" {{ old('role', $user->role)=='superadmin'?'selected':'' }}>superadmin</option></select></div>

  <h5>Permissions d'accès</h5>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="acces_reduction" name="acces[reduction]" value="1" {{ old('acces.reduction', $user->acces->reduction ?? 0) ? 'checked' : '' }}>
    <label class="form-check-label" for="acces_reduction">Réduction</label>
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="acces_antidate" name="acces[antidate]" value="1" {{ old('acces.antidate', $user->acces->antidate ?? 0) ? 'checked' : '' }}>
    <label class="form-check-label" for="acces_antidate">Antidater</label>
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="acces_modification" name="acces[modification]" value="1" {{ old('acces.modification', $user->acces->modification ?? 0) ? 'checked' : '' }}>
    <label class="form-check-label" for="acces_modification">Modification</label>
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="acces_entree" name="acces[entree]" value="1" {{ old('acces.entree', $user->acces->entree ?? 0) ? 'checked' : '' }}>
    <label class="form-check-label" for="acces_entree">Entrée</label>
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="acces_facturation" name="acces[facturation]" value="1" {{ old('acces.facturation', $user->acces->facturation ?? 0) ? 'checked' : '' }}>
    <label class="form-check-label" for="acces_facturation">Facturation</label>
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="acces_sortie" name="acces[sortie]" value="1" {{ old('acces.sortie', $user->acces->sortie ?? 0) ? 'checked' : '' }}>
    <label class="form-check-label" for="acces_sortie">Sortie</label>
  </div>

  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="blocked" name="blocked" value="1" {{ old('blocked', $user->blocked ?? 0) ? 'checked' : '' }}>
    <label class="form-check-label" for="blocked">Bloqué</label>
  </div>
  <div class="mb-3"><label>Compte caisse (optionnel)</label>
    <select name="caisse_compte_id" class="form-control">
      <option value="">-- Aucun --</option>
      @foreach($comptes as $c)
        <option value="{{ $c->id }}" {{ (old('caisse_compte_id', $user->caisse_compte_id) == $c->id) ? 'selected' : '' }}>{{ $c->numero }} - {{ $c->nom }}</option>
      @endforeach
    </select>
  </div>
    <button class="btn btn-success">Enregistrer</button>
    <button type="reset" class="btn btn-secondary ms-2">Réinitialiser</button>
</form>
@endsection
