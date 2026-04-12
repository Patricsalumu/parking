<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalCompte;

class JournalCompteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $rows = JournalCompte::with('compteDebit','compteCredit')->latest()->paginate(20);
        return view('journal_comptes.index', compact('rows'));
    }

    public function show(JournalCompte $journal_compte)
    {
        return view('journal_comptes.show', compact('journal_compte'));
    }
}
