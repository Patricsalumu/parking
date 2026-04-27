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
    <div>Exporté : {{ $exportDate ?? \Carbon\Carbon::now()->format('Y-m-d H:i') }}</div>
    @if(isset($compte_id) && $compte_id)
      @php $comp = \App\Models\Compte::find($compte_id); @endphp
      <div>Compte filtré : {{ $comp?->numero ?? '-' }} {{ $comp?->nom ? ' - '.$comp->nom : '' }}</div>
    @endif
    @if(isset($total_entrees))
      <div style="margin-top:6px"><strong>Total Entrées:</strong> {{ number_format($total_entrees,2,',',' ') }} &nbsp; | &nbsp; <strong>Total Sorties:</strong> {{ number_format($total_sorties ?? 0,2,',',' ') }} &nbsp; | &nbsp; <strong>Solde:</strong> {{ number_format($balance ?? 0,2,',',' ') }}</div>
    @endif
  </div>
  <h3 style="margin:4px 0 12px">Export Caisse</h3>

  <table style="width:100%; border-collapse:collapse; font-size:12px;">
    <thead>
      <tr style="background:#f0f0f0; text-align:left">
        <th style="padding:6px; border:1px solid #ddd">#</th>
        <th style="padding:6px; border:1px solid #ddd">Date</th>
        <th style="padding:6px; border:1px solid #ddd">Libellé</th>
        <th style="padding:6px; border:1px solid #ddd">Compte Débit</th>
        <th style="padding:6px; border:1px solid #ddd">Compte Crédit</th>
        <th style="padding:6px; border:1px solid #ddd">Montant</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
        <tr>
          <td style="padding:6px; border:1px solid #ddd">{{ $loop->iteration }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->date }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->libelle }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->compteDebit?->numero ?? '' }}{{ $r->compteDebit?->nom ? ' - '.$r->compteDebit->nom : '' }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ $r->compteCredit?->numero ?? '' }}{{ $r->compteCredit?->nom ? ' - '.$r->compteCredit->nom : '' }}</td>
          <td style="padding:6px; border:1px solid #ddd">{{ number_format($r->montant,2,',',' ') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
