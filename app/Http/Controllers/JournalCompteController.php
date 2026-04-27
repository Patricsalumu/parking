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
            if (abs($val) < 0.0001) continue;
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
            if (abs($val) < 0.0001) continue;
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

        // prepare per-account lists for the bilan presentation
        $assets_accounts = [];
        $passifs_accounts = [];

        $allComptes = Compte::with('classe')->orderBy('numero')->get();
        foreach ($allComptes as $c) {
            $debit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_debit_id',$c->id)->sum('montant');
            $credit = JournalCompte::when($start, fn($q) => $q->where('date','>=',$start))
                ->when($end, fn($q) => $q->where('date','<=',$end))
                ->where('compte_credit_id',$c->id)->sum('montant');
            $net = $debit - $credit;
            // skip zero balances
            if (abs($net) < 0.0001) {
                // still accumulate class totals for completeness
                $class_totals[$c->classe->numero] = ($class_totals[$c->classe->numero] ?? 0) + $net;
                continue;
            }
            // decide side and value contribution
            if (in_array((string)$c->classe->numero, $assets_classes)) {
                if ($net >= 0) {
                    $assets_accounts[] = ['compte'=>$c,'value'=>$net];
                    $assets_total += $net;
                } else {
                    $passifs_accounts[] = ['compte'=>$c,'value'=>abs($net)];
                    $passifs_total += abs($net);
                }
            } elseif (in_array((string)$c->classe->numero, $passifs_classes)) {
                if ($net <= 0) {
                    $passifs_accounts[] = ['compte'=>$c,'value'=>abs($net)];
                    $passifs_total += abs($net);
                } else {
                    $assets_accounts[] = ['compte'=>$c,'value'=>$net];
                    $assets_total += $net;
                }
            } else {
                // other classes (like 6,7) will be handled separately
                $class_totals[$c->classe->numero] = ($class_totals[$c->classe->numero] ?? 0) + $net;
            }
        }

        // Group by classe (first digit of compte.numero) and sort groups numerically
        $assets_groups = [];
        foreach ($assets_accounts as $a) {
            $num = preg_replace('/\D/', '', (string)$a['compte']->numero);
            $s = substr($num, 0, 1) ?: $num;
            if (!isset($assets_groups[$s])) { $assets_groups[$s] = ['label'=>$s,'total'=>0,'comptes'=>[]]; }
            $assets_groups[$s]['total'] += $a['value'];
            $assets_groups[$s]['comptes'][] = $a;
        }
        ksort($assets_groups, SORT_NUMERIC);

        $passifs_groups = [];
        foreach ($passifs_accounts as $p) {
            $num = preg_replace('/\D/', '', (string)$p['compte']->numero);
            $s = substr($num, 0, 1) ?: $num;
            if (!isset($passifs_groups[$s])) { $passifs_groups[$s] = ['label'=>$s,'total'=>0,'comptes'=>[]]; }
            $passifs_groups[$s]['total'] += $p['value'];
            $passifs_groups[$s]['comptes'][] = $p;
        }
        ksort($passifs_groups, SORT_NUMERIC);

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

        // add resultat to passifs_total (inscrire le compte de résultat au passif)
        $passifs_total += $resultat;

        return view('journal_comptes.bilan', compact('class_totals','total_charges','total_produits','resultat','start','end','assets_total','passifs_total','assets_accounts','passifs_accounts','assets_groups','passifs_groups'));
    }
}
