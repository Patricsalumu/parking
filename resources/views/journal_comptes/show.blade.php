@extends('layouts.app')

@section('content')
<h3>Écriture comptable #{{ $journal_compte->id }}</h3>
<div class="mb-2"><strong>Date:</strong> {{ $journal_compte->date }}</div>
<div class="mb-2"><strong>Libellé:</strong> {{ $journal_compte->libelle }}</div>
<div class="mb-2"><strong>Type:</strong> {{ $journal_compte->type }}</div>
<div class="mb-2"><strong>Référence:</strong> {{ $journal_compte->reference }}</div>

<table class="table table-sm mt-3">
	<thead><tr><th>Sens</th><th>Compte</th><th>Nom</th><th class="text-end">Montant</th></tr></thead>
	<tbody>
		<tr>
			<td>Débit</td>
			<td>{{ $journal_compte->compteDebit?->numero }}</td>
			<td>{{ $journal_compte->compteDebit?->nom }}</td>
			<td class="text-end">{{ number_format($journal_compte->montant,2) }}</td>
		</tr>
		<tr>
			<td>Crédit</td>
			<td>{{ $journal_compte->compteCredit?->numero }}</td>
			<td>{{ $journal_compte->compteCredit?->nom }}</td>
			<td class="text-end">{{ number_format($journal_compte->montant,2) }}</td>
		</tr>
	</tbody>
</table>
@endsection
