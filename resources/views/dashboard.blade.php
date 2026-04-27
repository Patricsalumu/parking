@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Tableau de bord</h1>
    <div>
      <a href="{{ route('entrees.create') }}" class="btn btn-primary me-2">New Entrée</a>
      <a href="{{ route('facturations.index') }}" class="btn btn-outline-secondary">Factures</a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
      <div class="card quick-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Entrées</h6>
          <h2 class="mb-0">{{ \App\Models\Entree::count() }}</h2>
          <small class="text-muted">Total enregistrements</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4">
      <div class="card quick-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Sorties</h6>
          <h2 class="mb-0">{{ \App\Models\Entree::whereNotNull('date_sortie')->count() }}</h2>
          <small class="text-muted">Véhicules sortis</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4">
      <div class="card quick-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Facturations</h6>
          <h2 class="mb-0">{{ \App\Models\Facturation::count() }}</h2>
          <small class="text-muted">Factures créées</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4">
      <div class="card quick-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Paiements</h6>
          <h2 class="mb-0">{{ \App\Models\Paiement::count() }}</h2>
          <small class="text-muted">Paiements enregistrés</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4">
      <div class="card quick-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Clients</h6>
          <h2 class="mb-0">{{ \App\Models\Client::count() }}</h2>
          <small class="text-muted">Clients actifs</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-4">
      <div class="card quick-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Véhicules</h6>
          <h2 class="mb-0">{{ \App\Models\Entree::whereNull('date_sortie')->count() }}</h2>
          <small class="text-muted">Véhicules actuellement présents</small>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5>Welcome, {{ auth()->user()->name }}</h5>
      <p class="text-muted">Use the menu to the left to manage entries, invoices and payments. Click the menu icon in the top bar to hide/show the sidebar; on desktop the state is remembered.</p>
    </div>
  </div>
</div>
@endsection
