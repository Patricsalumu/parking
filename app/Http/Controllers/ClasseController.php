<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classe;

class ClasseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function index()
    {
        $classes = Classe::latest()->paginate(20);
        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        return view('classes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'numero' => 'required|string',
            'type' => 'required|string',
        ]);
        Classe::create($data);
        return redirect()->route('classes.index')->with('success','Classe created');
    }

    public function edit(Classe $classe)
    {
        return view('classes.edit', compact('classe'));
    }

    public function update(Request $request, Classe $classe)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'numero' => 'required|string',
            'type' => 'required|string',
        ]);
        $classe->update($data);
        return redirect()->route('classes.index')->with('success','Classe updated');
    }

    public function destroy(Classe $classe)
    {
        $classe->delete();
        return redirect()->route('classes.index')->with('success','Classe deleted');
    }
}
