<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalCompte;
use App\Models\Compte;
use App\Models\Classe;
use Carbon\Carbon;

class JournalCompteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $rows = JournalCompte::with('compteDebit','compteCredit')->latest()->paginate(20);
        return view('journal_comptes.index', compact('rows'));
    }

    public function show(JournalCompte $journal_compte)
    {
        return view('journal_comptes.show', compact('journal_compte'));
    }

    // Grand Livre index: list comptes with totals
    public function grandLivreIndex(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        if (empty($start)) { $start = Carbon::today()->toDateString(); }
        if (empty($end)) { $end = Carbon::today()->toDateString(); }

        $comptes = Compte::with('classe')->orderBy('numero')->paginate(50);

        $data = [];
        foreach ($comptes as $c) {
            $debit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_debit_id', $c->id)->sum('montant');
            $credit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_credit_id', $c->id)->sum('montant');
            $data[$c->id] = ['compte'=>$c,'debit'=>$debit,'credit'=>$credit,'balance'=>$debit-$credit];
        }

        return view('journal_comptes.grand_index', compact('comptes','data','start','end'));
    }

    // Grand Livre show: details for a single compte
    public function grandLivreShow(Compte $compte, Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        if (empty($start)) { $start = Carbon::today()->toDateString(); }
        if (empty($end)) { $end = Carbon::today()->toDateString(); }

        $query = JournalCompte::with('compteDebit','compteCredit')
            ->where(function($q) use ($compte){
                $q->where('compte_debit_id', $compte->id)
                  ->orWhere('compte_credit_id', $compte->id);
            });
        $query->when($start, fn($q) => $q->where('date','>=',$start));
        $query->when($end, fn($q) => $q->where('date','<=',$end));

        $rows = $query->orderBy('date','desc')->paginate(50)->appends($request->query());

        // totals
        $total_debit = JournalCompte::where('compte_debit_id',$compte->id)
            ->when($start, fn($q) => $q->where('date','>=',$start))
            ->when($end, fn($q) => $q->where('date','<=',$end))
            ->sum('montant');
        $total_credit = JournalCompte::where('compte_credit_id',$compte->id)
            ->when($start, fn($q) => $q->where('date','>=',$start))
            ->when($end, fn($q) => $q->where('date','<=',$end))
            ->sum('montant');
        $balance = $total_debit - $total_credit;

        return view('journal_comptes.grand_show', compact('compte','rows','total_debit','total_credit','balance','start','end'));
    }

    // Trial balance / balances
    public function balances(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        if (empty($start)) { $start = Carbon::today()->toDateString(); }
        if (empty($end)) { $end = Carbon::today()->toDateString(); }

        $comptes = Compte::with('classe')->orderBy('numero')->get();
        $rows = [];
        $total_debit = 0; $total_credit = 0;
        foreach ($comptes as $c) {
            $debit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_debit_id', $c->id)->sum('montant');
            $credit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_credit_id', $c->id)->sum('montant');
            $bal = $debit - $credit;
            if ($bal >= 0) { $debit_val = $bal; $credit_val = 0; } else { $debit_val = 0; $credit_val = abs($bal); }
            $total_debit += $debit_val; $total_credit += $credit_val;
            $rows[] = ['compte'=>$c,'debit'=>$debit_val,'credit'=>$credit_val];
        }

        return view('journal_comptes.balances', compact('rows','total_debit','total_credit','start','end'));
    }

    // Compte de résultat (produits vs charges)
    public function compteResultat(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        if (empty($start)) { $start = Carbon::today()->toDateString(); }
        if (empty($end)) { $end = Carbon::today()->toDateString(); }

        // charges: classe 6, produits: classe 7
        $charges = Compte::whereHas('classe', fn($q)=> $q->where('numero','6'))->orderBy('numero')->get();
        $produits = Compte::whereHas('classe', fn($q)=> $q->where('numero','7'))->orderBy('numero')->get();

        $total_charges = 0; $total_produits = 0;
        $charges_data = []; $produits_data = [];
        foreach ($charges as $c) {
            $debit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_debit_id',$c->id)->sum('montant');
            $credit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_credit_id',$c->id)->sum('montant');
            $val = $debit - $credit;
            $total_charges += $val;
            $charges_data[] = ['compte'=>$c,'value'=>$val];
        }
        foreach ($produits as $c) {
            $debit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_debit_id',$c->id)->sum('montant');
            $credit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_credit_id',$c->id)->sum('montant');
            $val = $credit - $debit; // produits usually credit balance
            $total_produits += $val;
            $produits_data[] = ['compte'=>$c,'value'=>$val];
        }

        $resultat = $total_produits - $total_charges;
        return view('journal_comptes.compte_resultat', compact('charges_data','produits_data','total_charges','total_produits','resultat','start','end'));
    }

    // Bilan: simple aggregation by classes (assets vs passifs) and include resultat
    public function bilan(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        if (empty($start)) { $start = Carbon::today()->toDateString(); }
        if (empty($end)) { $end = Carbon::today()->toDateString(); }

        $classes = Classe::orderBy('numero')->get();
        $class_totals = [];

        // classification according to standard: passif = 1, actifs = 2,3,4,5, resultat = 6,7
        $assets_classes = ['2','3','4','5'];
        $passifs_classes = ['1'];

        $assets_total = 0; $passifs_total = 0;
        foreach ($classes as $cl) {
            $comptes = Compte::where('classe_id',$cl->id)->get();
            $sum = 0;
            foreach ($comptes as $c) {
                $debit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                    ->when($end, fn($q) => $q->where('date','<=',$end))
                    ->where('compte_debit_id',$c->id)->sum('montant');
                $credit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                    ->when($end, fn($q) => $q->where('date','<=',$end))
                    ->where('compte_credit_id',$c->id)->sum('montant');
                $net = $debit - $credit;
                $sum += $net;

                // accumulate to assets or passifs depending on class
                if (in_array((string)$cl->numero, $assets_classes)) {
                    // assets: positive net increases assets, negative net increases liabilities
                    if ($net >= 0) $assets_total += $net; else $passifs_total += abs($net);
                } elseif (in_array((string)$cl->numero, $passifs_classes)) {
                    // passifs: positive net (debit-credit) is unusual; treat credit excess as liabilities
                    if ($net <= 0) $passifs_total += abs($net); else $assets_total += $net;
                }
            }
            $class_totals[$cl->numero] = $sum;
        }

        // compute resultat from compteResultat logic (classes 6 & 7)
        $charges = Compte::whereHas('classe', fn($q)=> $q->where('numero','6'))->get();
        $produits = Compte::whereHas('classe', fn($q)=> $q->where('numero','7'))->get();
        $total_charges = 0; $total_produits = 0;
        foreach ($charges as $c) {
            $debit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_debit_id',$c->id)->sum('montant');
            $credit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_credit_id',$c->id)->sum('montant');
            $val = $debit - $credit;
            $total_charges += $val;
        }
        foreach ($produits as $c) {
            $debit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_debit_id',$c->id)->sum('montant');
            $credit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_credit_id',$c->id)->sum('montant');
            $val = $credit - $debit; // produits usually credit balance
            $total_produits += $val;
        }
        $resultat = $total_produits - $total_charges;

        // add resultat to passifs_total (equity side)
        if ($resultat >= 0) {
            $passifs_total += $resultat;
        } else {
            $assets_total += abs($resultat);
        }

        return view('journal_comptes.bilan', compact('class_totals','total_charges','total_produits','resultat','start','end','assets_total','passifs_total'));
    }
}
