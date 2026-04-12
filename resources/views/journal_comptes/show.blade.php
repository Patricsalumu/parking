@extends('layouts.app')

@section('content')
<h3>Journal Entry #{{ $journal_compte->id }}</h3>
<div class="mb-2"><strong>Date:</strong> {{ $journal_compte->date }}</div>
<div class="mb-2"><strong>Libellé:</strong> {{ $journal_compte->libelle }}</div>
<div class="mb-2"><strong>Montant:</strong> {{ number_format($journal_compte->montant,2) }}</div>
<div class="mb-2"><strong>Débit:</strong> {{ $journal_compte->compteDebit?->numero }} - {{ $journal_compte->compteDebit?->nom }}</div>
<div class="mb-2"><strong>Crédit:</strong> {{ $journal_compte->compteCredit?->numero }} - {{ $journal_compte->compteCredit?->nom }}</div>
@endsection
