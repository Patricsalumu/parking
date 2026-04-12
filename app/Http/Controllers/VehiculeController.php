<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicule;
use App\Models\Client;

class VehiculeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $vehicules = Vehicule::latest()->paginate(15);
        return view('vehicules.index', compact('vehicules'));
    }

    public function create()
    {
        $clients = Client::all();
        return view('vehicules.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plaque' => 'required|string|unique:vehicules,plaque',
            'marque' => 'nullable|string',
            'compagnie' => 'nullable|string',
            'pays' => 'nullable|string',
            'essieux' => 'nullable|integer',
            'client_id' => 'nullable|exists:clients,id',
        ]);
        Vehicule::create($data);
        return redirect()->route('vehicules.index')->with('success','Vehicule created');
    }

    public function edit(Vehicule $vehicule)
    {
        $clients = Client::all();
        return view('vehicules.edit', compact('vehicule','clients'));
    }

    public function update(Request $request, Vehicule $vehicule)
    {
        $data = $request->validate([
            'plaque' => 'required|string|unique:vehicules,plaque,'.$vehicule->id,
            'marque' => 'nullable|string',
            'compagnie' => 'nullable|string',
            'pays' => 'nullable|string',
            'essieux' => 'nullable|integer',
            'client_id' => 'nullable|exists:clients,id',
        ]);
        $vehicule->update($data);
        return redirect()->route('vehicules.index')->with('success','Vehicule updated');
    }

    public function destroy(Vehicule $vehicule)
    {
        $vehicule->delete();
        return redirect()->route('vehicules.index')->with('success','Vehicule deleted');
    }
}
