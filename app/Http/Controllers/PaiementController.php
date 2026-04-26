<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paiement;
use App\Models\Facturation;
use Carbon\Carbon;
use App\Models\JournalCompte;
use App\Models\Compte;

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
            'user_id' => auth()->id(),
        ]);

        // update facturation
        $fact = Facturation::find($data['facturation_id']);
        // preserve original updated_at to avoid touching the facture's updated timestamp on payment
        $originalUpdatedAt = $fact->getOriginal('updated_at');
        $fact->montant_paye = ($fact->montant_paye ?? 0) + $data['montant'];
        if ($fact->montant_paye >= $fact->montant_total) {
            $fact->date_paiement = $paiement->date_paiement;
        }
        // save without updating timestamps so updated_at remains unchanged
        $fact->timestamps = false;
        $fact->save();
        $fact->timestamps = true;
        // ensure model has original updated_at in memory
        $fact->setRawAttributes(array_merge($fact->getAttributes(), ['updated_at' => $originalUpdatedAt]));

        // Create accounting entry for the payment: debit caisse (user's caisse_compte_id), credit client (411000)
        try {
            $clientCompte = Compte::where('numero','411000')->first();
            $userCaisseId = auth()->user()->caisse_compte_id;
            if ($clientCompte && $userCaisseId) {
                JournalCompte::create([
                    'libelle' => 'Paiement facture #'.$fact->id,
                    'montant' => $paiement->montant,
                    'date' => $paiement->date_paiement->toDateString(),
                    'compte_debit_id' => $userCaisseId,
                    'compte_credit_id' => $clientCompte->id,
                ]);
            }
        } catch(\Exception $e) {
            \Log::error('Accounting entry failed for paiement '.$paiement->id.': '.$e->getMessage());
        }

        // On normal request, redirect to facturations index (not paiements index)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('facturations.index'),
                'message' => 'Paiement enregistré'
            ]);
        }

        return redirect()->route('facturations.index')->with('success','Paiement enregistré');
    }
}
