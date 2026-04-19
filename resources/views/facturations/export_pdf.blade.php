@php
  $entreprise = \App\Models\Entreprise::first();
  $logoPath = $entreprise && $entreprise->logo ? public_path('storage/' . $entreprise->logo) : null;
@endphp

<div style="font-family: Arial, Helvetica, sans-serif; max-width:900px; margin:0 auto;">
  <div style="display:flex; align-items:center; gap:12px;">
    @if($logoPath && file_exists($logoPath))
      <div><img src="{{ $logoPath }}" style="height:64px; object-fit:contain"/></div>
    @endif
    <div>
      <div style="font-size:18px;font-weight:700">{{ $entreprise->nom ?? 'Entreprise' }}</div>
      <div style="font-size:12px">{{ $entreprise->slogan ?? '' }}</div>
      <div style="font-size:12px">{{ $entreprise->telephone ?? '' }} {{ $entreprise->adresse ? '· '.$entreprise->adresse : '' }}</div>
    </div>
  </div>
  <hr style="margin:8px 0 12px"/>
  <div style="font-size:12px;margin-bottom:6px">
    <div>Période : {{ $start ?? '-' }} → {{ $end ?? '-' }}</div>
    <div>Exporté : {{ $exportDate ?? format_dt(\Carbon\Carbon::now()) }}</div>
  </div>
  <h3 style="margin:4px 0 12px">Export Facturations</h3>

  <table style="width:100%; border-collapse:collapse; font-size:12px;">
    <thead>
      <tr style="background:#f0f0f0; text-align:left">
        <th style="padding:6px; border:1px solid #ddd">ID</th>
        <th style="padding:6px; border:1px solid #ddd">Date</th>
        <th style="padding:6px; border:1px solid #ddd">Entrée</th>
        <th style="padding:6px; border:1px solid #ddd">Plaque</th>
        <th style="padding:6px; border:1px solid #ddd">Client</th>
        <th style="padding:6px; border:1px solid #ddd">Catégorie</th>
        <th style="padding:6px; border:1px solid #ddd; text-align:right">Total</th>
        <th style="padding:6px; border:1px solid #ddd; text-align:right">Payé</th>
        <th style="padding:6px; border:1px solid #ddd; text-align:right">Reste</th>
        <th style="padding:6px; border:1px solid #ddd">Utilisateur</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->id }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->created_at ? format_dt($r->created_at) : '' }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->entree_id }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->entree?->vehicule?->plaque }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->entree?->client?->nom }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->entree?->categorie?->nom ?? $r->categorie?->nom }}</td>
          <td style="padding:6px; border:1px solid #ddd; text-align:right">{{ number_format($r->montant_total,2) }}</td>
          <td style="padding:6px; border:1px solid #ddd; text-align:right">{{ number_format($r->montant_paye,2) }}</td>
          <td style="padding:6px; border:1px solid #ddd; text-align:right">{{ number_format(($r->montant_total - ($r->montant_paye ?? 0)),2) }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->user?->name }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

