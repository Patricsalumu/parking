<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entree;
use App\Models\Client;
use App\Models\Vehicule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EntreeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $entrees = Entree::with('vehicule','client','user')->latest()->paginate(20);
        return view('entrees.index', compact('entrees'));
    }

    public function create()
    {
        $clients = Client::all();
        $vehicules = Vehicule::all();
        return view('entrees.create', compact('clients','vehicules'));
    }

    public function store(Request $request)
    {
        // check access
        if (!in_array(auth()->user()->role, ['superadmin'])) {
            $acc = auth()->user()->acces;
            if (!$acc || !$acc->entree) {
                abort(403,'Unauthorized');
            }
        }
        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_nom' => 'nullable|string',
            'vehicule_id' => 'nullable|exists:vehicules,id',
            'plaque' => 'nullable|string',
            'observation' => 'nullable|string',
            'qr_code' => 'nullable|string',
        ]);

        // create/select client inline
        if (empty($data['client_id']) && !empty($data['client_nom'])) {
            $client = Client::create(['nom'=>$data['client_nom']]);
            $data['client_id'] = $client->id;
        }

        // create/select vehicule inline
        if (empty($data['vehicule_id']) && !empty($data['plaque'])) {
            $vehicule = Vehicule::firstOrCreate(['plaque'=> $data['plaque']], ['marque'=>null]);
            $data['vehicule_id'] = $vehicule->id;
        }

        $entree = Entree::create([
            'user_id' => Auth::id(),
            'vehicule_id' => $data['vehicule_id'],
            'client_id' => $data['client_id'] ?? null,
            'date_entree' => Carbon::now(),
            'observation' => $data['observation'] ?? null,
            'qr_code' => $data['qr_code'] ?? null,
        ]);

        return redirect()->route('entrees.index')->with('success','Entrée enregistrée');
    }

    public function edit(Entree $entree)
    {
        $clients = Client::all();
        $vehicules = Vehicule::all();
        return view('entrees.edit', compact('entree','clients','vehicules'));
    }

    public function update(Request $request, Entree $entree)
    {
        // check modification access
        if (!in_array(auth()->user()->role, ['superadmin'])) {
            $acc = auth()->user()->acces;
            if (!$acc || !$acc->modification) {
                abort(403,'Unauthorized');
            }
        }
        $data = $request->validate([
            'date_sortie' => 'nullable|date',
            'observation' => 'nullable|string',
        ]);
        if (!empty($data['date_sortie'])) {
            $entree->date_sortie = Carbon::parse($data['date_sortie']);
        }
        $entree->observation = $data['observation'] ?? $entree->observation;
        $entree->save();
        return redirect()->route('entrees.index')->with('success','Entrée mise à jour');
    }

    public function destroy(Entree $entree)
    {
        $entree->delete();
        return redirect()->route('entrees.index')->with('success','Entrée supprimée');
    }
}
