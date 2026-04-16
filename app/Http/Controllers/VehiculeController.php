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

    // JSON endpoint to find a vehicule by plaque (plate)
    public function findByPlaque(Request $request)
    {
        $plaque = $request->query('plaque');
        if (empty($plaque)) {
            return response()->json(['found' => false]);
        }
        $vehicule = Vehicule::with('client')->where('plaque', $plaque)->first();
        if (!$vehicule) {
            return response()->json(['found' => false]);
        }
        return response()->json(['found' => true, 'vehicule' => $vehicule]);
    }

    // AJAX endpoint: search matching plaques by prefix or substring (for suggestions)
    public function searchPlaques(Request $request)
    {
        $q = $request->query('q');
        if (empty($q)) return response()->json(['results' => []]);
        $q = trim($q);
        // Find matching vehicles, then for each vehicle try to find an open entree (no date_sortie).
        $vehicles = Vehicule::where('plaque','like','%'.$q.'%')
            ->orderBy('plaque')
            ->limit(50)
            ->get();

        $results = [];
        foreach ($vehicles as $v) {
            // prefer open entree
            $entree = \App\Models\Entree::with('client')
                ->where('vehicule_id', $v->id)
                ->whereNull('date_sortie')
                ->latest('date_entree')
                ->first();
            if (!$entree) {
                $entree = \App\Models\Entree::with('client')
                    ->where('vehicule_id', $v->id)
                    ->latest('date_entree')
                    ->first();
            }
            $results[] = [
                'plaque' => $v->plaque,
                'pays' => $v->pays,
                'compagnie' => $v->compagnie,
                'client' => $entree?->client?->nom ?? $v->client?->nom ?? null,
                'has_open' => $entree && $entree->date_sortie === null,
                'entree_id' => $entree?->id ?? null,
            ];
        }

        // sort: open entries first, then by plaque
        usort($results, function($a,$b){
            if ($a['has_open'] && !$b['has_open']) return -1;
            if (!$a['has_open'] && $b['has_open']) return 1;
            return strcmp($a['plaque'],$b['plaque']);
        });

        return response()->json(['results' => array_slice($results,0,15)]);
    }
}
