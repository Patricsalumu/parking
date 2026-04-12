<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compte;
use App\Models\Classe;

class CompteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function index()
    {
        $comptes = Compte::with('classe')->latest()->paginate(20);
        return view('comptes.index', compact('comptes'));
    }

    public function create()
    {
        $classes = Classe::all();
        return view('comptes.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'numero' => 'required|string',
            'classe_id' => 'required|exists:classes,id',
        ]);
        Compte::create($data);
        return redirect()->route('comptes.index')->with('success','Compte created');
    }

    public function edit(Compte $compte)
    {
        $classes = Classe::all();
        return view('comptes.edit', compact('compte','classes'));
    }

    public function update(Request $request, Compte $compte)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'numero' => 'required|string',
            'classe_id' => 'required|exists:classes,id',
        ]);
        $compte->update($data);
        return redirect()->route('comptes.index')->with('success','Compte updated');
    }

    public function destroy(Compte $compte)
    {
        $compte->delete();
        return redirect()->route('comptes.index')->with('success','Compte deleted');
    }
}
