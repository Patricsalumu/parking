<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paiement;
use App\Models\Facturation;
use Carbon\Carbon;

class PaiementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $paiements = Paiement::with('facturation')->latest()->paginate(20);
        return view('paiements.index', compact('paiements'));
    }

    public function create($facturation_id)
    {
        $facturation = Facturation::findOrFail($facturation_id);
        return view('paiements.create', compact('facturation'));
    }

    public function store(Request $request)
    {
        // access
        if (!in_array(auth()->user()->role, ['superadmin'])) {
            $acc = auth()->user()->acces;
            if (!$acc || !$acc->facturation) {
                abort(403,'Unauthorized');
            }
        }
        $data = $request->validate([
            'facturation_id' => 'required|exists:facturations,id',
            'montant' => 'required|numeric|min:0.01',
            'date_paiement' => 'nullable|date',
            'mode' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $paiement = Paiement::create([
            'facturation_id' => $data['facturation_id'],
            'montant' => $data['montant'],
            'date_paiement' => $data['date_paiement'] ?? Carbon::now(),
            'mode' => $data['mode'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        // update facturation
        $fact = Facturation::find($data['facturation_id']);
        $fact->montant_paye = ($fact->montant_paye ?? 0) + $data['montant'];
        if ($fact->montant_paye >= $fact->montant_total) {
            $fact->date_paiement = $paiement->date_paiement;
        }
        $fact->save();

        return redirect()->route('paiements.index')->with('success','Paiement enregistré');
    }
}
