<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categorie;
use App\Models\Compte;

class CategorieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function index()
    {
        $categories = Categorie::with('compteProduit')->latest()->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $comptes = Compte::orderBy('numero')->get();
        return view('categories.create', compact('comptes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prix_par_24h' => 'required|numeric',
            'compte_produit_id' => 'required|exists:comptes,id',
        ]);
        Categorie::create($data);
        return redirect()->route('categories.index')->with('success','Categorie créée');
    }

    public function edit(Categorie $category)
    {
        $comptes = Compte::orderBy('numero')->get();
        return view('categories.edit', ['category' => $category, 'comptes' => $comptes]);
    }

    public function update(Request $request, Categorie $category)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prix_par_24h' => 'required|numeric',
            'compte_produit_id' => 'required|exists:comptes,id',
        ]);
        $category->update($data);
        return redirect()->route('categories.index')->with('success','Categorie mise à jour');
    }

    public function destroy(Categorie $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success','Categorie supprimée');
    }
}
