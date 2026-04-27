<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalCompte;
use App\Models\Compte;
use Carbon\Carbon;

class CaisseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $q = $request->query('q');
        $compte_id = $request->query('compte_id');
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        // default to today when not provided
        if (empty($start)) { $start = Carbon::today()->toDateString(); }
        if (empty($end)) { $end = Carbon::today()->toDateString(); }

        $comptes = Compte::orderBy('numero')->get();

        $query = JournalCompte::with(['compteDebit','compteCredit']);

        if ($compte_id) {
            $query->where(function($sub) use ($compte_id){
                $sub->where('compte_debit_id', $compte_id)
                    ->orWhere('compte_credit_id', $compte_id);
            });
        }

        if ($start) {
            $query->where('date', '>=', $start);
        }
        if ($end) {
            $query->where('date', '<=', $end);
        }

        if ($q) {
            $query->where('libelle', 'like', "%{$q}%");
        }

        $entries = $query->orderBy('date','desc')->orderBy('id','desc')->paginate(50)->appends($request->query());

        // totals
        if ($compte_id) {
            $total_entrees = JournalCompte::where('compte_debit_id', $compte_id)
                ->when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($request->query('q'), fn($q2) => $q2->where('libelle','like','%'.$request->query('q').'%'))
                ->sum('montant');

            $total_sorties = JournalCompte::where('compte_credit_id', $compte_id)
                ->when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($request->query('q'), fn($q2) => $q2->where('libelle','like','%'.$request->query('q').'%'))
                ->sum('montant');
        } else {
            $total_entrees = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($q, fn($q2) => $q2->where('libelle','like','%'.$q.'%'))
                ->sum('montant');

            $total_sorties = $total_entrees; // for full-set, debits == credits in journal; show same total
        }

        $balance = $total_entrees - $total_sorties;

        return view('caisse.index', compact('entries','comptes','compte_id','start','end','q','total_entrees','total_sorties','balance'));
    }

    public function storeSortie(Request $request)
    {
        $data = $request->validate([
            'compte_debit_id' => 'required|exists:comptes,id',
            'montant' => 'required|numeric|min:0.01',
            'libelle' => 'required|string',
            'date' => 'nullable|date',
            'type' => 'nullable|in:Banques,caisses,ventes,achat,OD',
            'reference' => 'nullable|string',
        ]);

        $user = auth()->user();
        $caisseId = $user->caisse_compte_id;
        if (!$caisseId) {
            return redirect()->back()->with('error','Votre utilisateur n\'a pas de compte caisse configuré.');
        }

        $date = $data['date'] ?? Carbon::today()->toDateString();

        try {
            JournalCompte::create([
                'libelle' => $data['libelle'],
                'montant' => $data['montant'],
                'date' => $date,
                'compte_debit_id' => $data['compte_debit_id'],
                'compte_credit_id' => $caisseId,
                'type' => $data['type'] ?? 'caisses',
                'reference' => $data['reference'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create sortie journal entry: '.$e->getMessage());
            return redirect()->back()->with('error','Impossible d\'enregistrer l\'écriture.');
        }

        return redirect()->route('caisse.index')->with('success','Sortie enregistrée en caisse.');
    }
}
