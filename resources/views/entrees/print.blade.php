<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Bon d'entrée #{{ $entree->numero_formatted ?? $entree->numero ?? $entree->id }}</title>
  <style>
    body{ font-family: Arial, Helvetica, sans-serif; width:80mm; margin:0; padding:8px; }
    .company{ text-align:center; font-weight:700; }
    .slogan{ text-align:center; font-size:0.9em; color:#555 }
    .small{ font-size:0.9em }
    .tbl{ width:100%; margin-top:8px }
    .tbl td{ padding:3px 0 }
    .qr{ text-align:center; margin:8px 0 }
    @media print { body{ width:80mm; } }
  </style>
</head>
<body>
  <div style="margin-bottom:8px;">
    <a href="{{ route('entrees.create') }}" style="display:inline-block;padding:6px 10px;border:1px solid #000;border-radius:4px;text-decoration:none;color:#000;font-size:0.9em;">← Retour</a>
  </div>
  <div class="company">{{ $entreprise->nom ?? config('app.name') }}</div>
  <div class="slogan">{{ $entreprise->slogan ?? '' }}</div>
  <div style="border-bottom:1px solid #000; margin:6px 0"></div>

  <div class="qr" style="text-align:center;">
    @php
      $qrAvailable = class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class);
    @endphp
    @if($qrAvailable)
      {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate(route('entrees.print', $entree)) !!}
    @else
      <div style="font-size:0.9em;color:#a00">QR unavailable (install package)</div>
    @endif
  </div>

  <div class="tbl" style="text-align:center; margin-top:6px">
    <table style="margin:0 auto;">
      <tr><td class="small">Bon d'entrée N°</td><td class="small">: {{ $entree->numero_formatted ?? $entree->numero ?? $entree->id }}</td></tr>
      <tr><td class="small">Date d'entrée</td><td class="small">: {{ $entree->date_entree ? \Carbon\Carbon::parse($entree->date_entree)->format('Y-m-d H:i') : '' }}</td></tr>
      <tr><td class="small">Compagnie</td><td class="small">: {{ $entree->vehicule?->compagnie }}</td></tr>
      <tr><td class="small">Plaque</td><td class="small">: {{ $entree->vehicule?->plaque }}</td></tr>
      <tr><td class="small">Enregistré par</td><td class="small">: {{ $entree->user?->name }}</td></tr>
    </table>
  </div>

  <script>
    // auto open print dialog
    window.onload = function(){ setTimeout(function(){ window.print() }, 300); };
  </script>
</body>
</html>