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
  <h3 style="margin:4px 0 12px">Export Entrées</h3>

  <table style="width:100%; border-collapse:collapse; font-size:12px;">
    <thead>
      <tr style="background:#f0f0f0; text-align:left">
        <th style="padding:6px; border:1px solid #ddd">ID</th>
        <th style="padding:6px; border:1px solid #ddd">Date Entrée</th>
        <th style="padding:6px; border:1px solid #ddd">Plaque</th>
        <th style="padding:6px; border:1px solid #ddd">Compagnie</th>
        <th style="padding:6px; border:1px solid #ddd">Client</th>
        <th style="padding:6px; border:1px solid #ddd">Utilisateur</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->id }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->date_entree ? format_dt($r->date_entree) : '' }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->vehicule?->plaque }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->vehicule?->compagnie }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->client?->nom }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->user?->name }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

