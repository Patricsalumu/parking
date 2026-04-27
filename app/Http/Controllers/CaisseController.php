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

        // Only load 'caisse' accounts (class number 5) for the filter
        $comptes = Compte::whereHas('classe', function($q){
            $q->where('numero','5');
        })->orderBy('numero')->get();

        // comptes allowed to be debited on a sortie: classes 5 and 6 (charges & financiers)
        $comptes_debit = Compte::whereHas('classe', function($q){
            $q->whereIn('numero',['5','6']);
        })->orderBy('numero')->get();

        // comptes allowed to be credited on a sortie: class 5 only (caisses/finances)
        $comptes_credit = Compte::whereHas('classe', function($q){
            $q->where('numero','5');
        })->orderBy('numero')->get();

        $query = JournalCompte::with(['compteDebit','compteCredit']);

        // default to the connected user's caisse account when none selected
        if (empty($compte_id)) {
            $compte_id = auth()->user()->caisse_compte_id ?? null;
        }

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

        $entries = $query->orderBy('date','desc')->orderBy('id','desc')->paginate(15);
        $entries->appends($request->query());

        $selectedCompte = null;
        if ($compte_id) {
            $selectedCompte = Compte::find($compte_id);
        }

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

        return view('caisse.index', compact('entries','comptes','compte_id','start','end','q','total_entrees','total_sorties','balance','selectedCompte','comptes_debit','comptes_credit'));
    }

    public function exportCsv(Request $request)
    {
        $q = $request->query('q');
        $compte_id = $request->query('compte_id');
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        // apply same defaults as index: default dates to today and default compte_id to user's caisse
        if (empty($start)) { $start = Carbon::today()->toDateString(); }
        if (empty($end)) { $end = Carbon::today()->toDateString(); }
        if (empty($compte_id)) { $compte_id = auth()->user()->caisse_compte_id ?? null; }

        $query = JournalCompte::with(['compteDebit','compteCredit']);
        if ($compte_id) {
            $query->where(function($sub) use ($compte_id){
                $sub->where('compte_debit_id', $compte_id)
                    ->orWhere('compte_credit_id', $compte_id);
            });
        }
        if ($start) $query->where('date','>=',$start);
        if ($end) $query->where('date','<=',$end);
        if ($q) $query->where('libelle','like','%'.$q.'%');

        $rows = $query->orderBy('date','desc')->orderBy('id','desc')->get();

        // compute totals like on the index page
        if ($compte_id) {
            $total_entrees = JournalCompte::where('compte_debit_id', $compte_id)
                ->when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($q, fn($q2) => $q2->where('libelle','like','%'.$q.'%'))
                ->sum('montant');

            $total_sorties = JournalCompte::where('compte_credit_id', $compte_id)
                ->when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($q, fn($q2) => $q2->where('libelle','like','%'.$q.'%'))
                ->sum('montant');
        } else {
            $total_entrees = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($q, fn($q2) => $q2->where('libelle','like','%'.$q.'%'))
                ->sum('montant');

            $total_sorties = $total_entrees;
        }

        $balance = $total_entrees - $total_sorties;

        $filename = 'caisse_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($rows, $start, $end, $total_entrees, $total_sorties, $balance) {
            $out = fopen('php://output','w');
            fputcsv($out, ['Export Date', \Carbon\Carbon::now()->format('Y-m-d H:i')]);
            fputcsv($out, ['Start Date', $start]);
            fputcsv($out, ['End Date', $end]);
            fputcsv($out, []);
            fputcsv($out, ['Total Entrées', $total_entrees]);
            fputcsv($out, ['Total Sorties', $total_sorties]);
            fputcsv($out, ['Solde', $balance]);
            fputcsv($out, []);
            fputcsv($out, ['ID','Date','Libelle','Compte Debit','Compte Credit','Montant']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->date,
                    $r->libelle,
                    $r->compteDebit?->numero . ' - ' . ($r->compteDebit?->nom ?? ''),
                    $r->compteCredit?->numero . ' - ' . ($r->compteCredit?->nom ?? ''),
                    $r->montant,
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $q = $request->query('q');
        $compte_id = $request->query('compte_id');
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        if (empty($start)) { $start = Carbon::today()->toDateString(); }
        if (empty($end)) { $end = Carbon::today()->toDateString(); }
        if (empty($compte_id)) { $compte_id = auth()->user()->caisse_compte_id ?? null; }

        $query = JournalCompte::with(['compteDebit','compteCredit']);
        if ($compte_id) {
            $query->where(function($sub) use ($compte_id){
                $sub->where('compte_debit_id', $compte_id)
                    ->orWhere('compte_credit_id', $compte_id);
            });
        }
        if ($start) $query->where('date','>=',$start);
        if ($end) $query->where('date','<=',$end);
        if ($q) $query->where('libelle','like','%'.$q.'%');

        $rows = $query->orderBy('date','desc')->orderBy('id','desc')->get();

        // compute totals like on the index page
        if ($compte_id) {
            $total_entrees = JournalCompte::where('compte_debit_id', $compte_id)
                ->when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($q, fn($q2) => $q2->where('libelle','like','%'.$q.'%'))
                ->sum('montant');

            $total_sorties = JournalCompte::where('compte_credit_id', $compte_id)
                ->when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($q, fn($q2) => $q2->where('libelle','like','%'.$q.'%'))
                ->sum('montant');
        } else {
            $total_entrees = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->when($q, fn($q2) => $q2->where('libelle','like','%'.$q.'%'))
                ->sum('montant');

            $total_sorties = $total_entrees;
        }

        $balance = $total_entrees - $total_sorties;

        $exportDate = \Carbon\Carbon::now()->format('Y-m-d H:i');
        if (class_exists(\Barryvdh\DomPDF\PDF::class) || class_exists(\Barryvdh\DomPDF\Facade::class)) {
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadView('caisse.export_pdf', compact('rows','start','end','exportDate','compte_id','total_entrees','total_sorties','balance'));
            return $pdf->download('caisse_'.now()->format('Ymd_His').'.pdf');
        }
        return view('caisse.export_pdf', compact('rows','start','end','exportDate','compte_id','total_entrees','total_sorties','balance'));
    }

    public function storeSortie(Request $request)
    {
        $data = $request->validate([
            'compte_debit_id' => 'required|exists:comptes,id',
            'compte_credit_id' => 'required|exists:comptes,id',
            'montant' => 'required|numeric|min:0.01',
            'libelle' => 'required|string',
            'date' => 'nullable|date',
            'type' => 'nullable|in:Banques,caisses,ventes,achat,OD',
            'reference' => 'nullable|string',
        ]);

        // verify selected comptes have allowed classes
        $compteDebit = Compte::with('classe')->find($data['compte_debit_id']);
        $compteCredit = Compte::with('classe')->find($data['compte_credit_id']);

        if (!$compteDebit || !in_array($compteDebit->classe->numero, ['5','6'])) {
            return redirect()->back()->with('error','Le compte à débiter doit être de classe 5 ou 6.');
        }

        if (!$compteCredit || $compteCredit->classe->numero !== '5') {
            return redirect()->back()->with('error','Le compte à créditer doit être un compte de caisse (classe 5).');
        }

        $date = $data['date'] ?? Carbon::today()->toDateString();

        try {
            JournalCompte::create([
                'libelle' => $data['libelle'],
                'montant' => $data['montant'],
                'date' => $date,
                'compte_debit_id' => $data['compte_debit_id'],
                'compte_credit_id' => $data['compte_credit_id'],
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
