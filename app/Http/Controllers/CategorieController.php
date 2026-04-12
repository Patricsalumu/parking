<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categorie;

class CategorieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function index()
    {
        $categories = Categorie::latest()->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prix_par_24h' => 'required|numeric',
        ]);
        Categorie::create($data);
        return redirect()->route('categories.index')->with('success','Categorie créée');
    }

    public function edit(Categorie $category)
    {
        return view('categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Categorie $category)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prix_par_24h' => 'required|numeric',
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
