@extends('layouts.app')

@section('content')
<h3>Register Payment for Facturation #{{ $facturation->numero_formatted ?? $facturation->numero ?? $facturation->id }}</h3>
<form id="paiementForm" method="POST" action="{{ route('paiements.store') }}">
  @csrf
  <input type="hidden" name="facturation_id" value="{{ $facturation->id }}">
  <div class="mb-3"><label>Montant</label><input name="montant" class="form-control"></div>
  @if(!empty($canAntidate))
    <div class="mb-3">
      <label>Date et heure du paiement</label>
      <input name="date_paiement" type="datetime-local" class="form-control" value="{{ old('date_paiement', now()->format('Y-m-d\\TH:i')) }}">
      <div class="form-text">Avec l'accès antidate, cette valeur fixe aussi created_at et updated_at.</div>
    </div>
  @endif
  <div class="mb-3"><label>Mode</label><input name="mode" class="form-control"></div>
  <button class="btn btn-success">Save Payment</button>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('paiementForm');
  form.addEventListener('submit', function(e){
    e.preventDefault();
    const url = form.action;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const data = {};
    new FormData(form).forEach((v,k)=>{ data[k]=v; });
    fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token || '',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    }).then(r => r.json()).then(resp => {
      if (resp && resp.success) {
        // load facturations index via AJAX and replace main content
        fetch(resp.redirect_url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
          .then(r => r.text())
          .then(html => {
            try {
              const parser = new DOMParser();
              const doc = parser.parseFromString(html, 'text/html');
              const newMain = doc.querySelector('main');
              if (newMain) {
                document.querySelector('main').innerHTML = newMain.innerHTML;
                // initialize toasts for newly injected content
                if (window.initToasts) window.initToasts();
                // also show explicit message from response if present
                if (resp.message && window.showToast) window.showToast(resp.message, 'success');
              } else {
                // fallback: full redirect
                window.location.href = resp.redirect_url;
              }
            } catch(err) {
              console.error('Inject facturations error', err);
              window.location.href = resp.redirect_url;
            }
          }).catch(err => { console.error('Fetch facturations failed', err); window.location.href = resp.redirect_url; });
      } else {
        alert(resp?.message || 'Erreur lors de l\'enregistrement du paiement');
      }
    }).catch(err => {
      console.error('Paiement submit error', err);
      alert('Erreur réseau lors de l\'enregistrement du paiement');
    });
  });
});
</script>
</push>
@endsection
