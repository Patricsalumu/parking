@extends('layouts.app')

@section('content')
<h3>Entreprise</h3>
<form method="POST" enctype="multipart/form-data" action="{{ route('settings.entreprise.save') }}">
  @csrf
  <div class="mb-3"><label>Nom</label><input name="nom" value="{{ $entreprise->nom ?? '' }}" class="form-control"></div>
  <div class="row">
    <div class="col-md-4 mb-3"><label>Devise</label>
      <select name="devise" class="form-control">
        <option value="$" {{ (isset($entreprise) && $entreprise->devise=='$')? 'selected' : '' }}>$</option>
        <option value="Fc" {{ (isset($entreprise) && $entreprise->devise=='Fc')? 'selected' : '' }}>Fc</option>
      </select>
    </div>
    <div class="col-md-4 mb-3"><label>Taux de change</label><input name="taux_change" value="{{ $entreprise->taux_change ?? '' }}" class="form-control"></div>
    <div class="col-md-4 mb-3"><label>Telephone</label><input name="telephone" value="{{ $entreprise->telephone ?? '' }}" class="form-control"></div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3"><label>RCCM</label><input name="rccm" value="{{ $entreprise->rccm ?? '' }}" class="form-control"></div>
    <div class="col-md-6 mb-3"><label>ID National</label><input name="id_nat" value="{{ $entreprise->id_nat ?? '' }}" class="form-control"></div>
  </div>

  <div class="mb-3"><label>Numéro d'impôt</label><input name="num_impot" value="{{ $entreprise->num_impot ?? '' }}" class="form-control"></div>

  <div class="mb-3"><label>Slogan</label><input name="slogan" value="{{ $entreprise->slogan ?? '' }}" class="form-control"></div>

  <div class="mb-3"><label>Logo</label>
    @if(isset($entreprise) && $entreprise->logo)
      <div class="mb-2"><img src="{{ asset('storage/' . $entreprise->logo) }}" alt="logo" style="max-height:80px;"></div>
    @endif
    <input type="file" name="logo" class="form-control">
  </div>

  <div class="mb-3"><label>Adresse</label><textarea name="adresse" class="form-control">{{ $entreprise->adresse ?? '' }}</textarea></div>
  <button class="btn btn-success">Save</button>
</form>
@endsection
