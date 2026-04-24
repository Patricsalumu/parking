<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facturation;
use App\Models\Entree;
use App\Models\Categorie;
use Carbon\Carbon;
use App\Models\JournalCompte;
use App\Models\Compte;

class FacturationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $query = Facturation::with('entree.vehicule','categorie');

        // search query: if it's a plaque-like token (no spaces) we will ignore date filters
        $q = request()->input('q');
        $isPlaqueSearch = $q && preg_match('/^[A-Za-z0-9\-]+$/', $q);
        if ($q) {
            $query->whereHas('entree.vehicule', function($qv) use ($q){ $qv->where('plaque','like','%'.$q.'%'); })
                  ->orWhereHas('entree.client', function($qc) use ($q){ $qc->where('nom','like','%'.$q.'%'); })
                  ->orWhereHas('user', function($qu) use ($q){ $qu->where('name','like','%'.$q.'%'); });
        }

        // date range filter on facture created_at (defaults to today) - skip when plaque search
        $start = request()->input('start_date', now()->format('Y-m-d'));
        $end = request()->input('end_date', now()->format('Y-m-d'));
        if (!$isPlaqueSearch) {
            if ($start) $query->whereDate('created_at', '>=', $start);
            if ($end) $query->whereDate('created_at', '<=', $end);
        }

        $facturations = $query->latest()->paginate(20);
        $facturations->appends(request()->all());

        // compute totals for current filters (not just page)
        $all = (clone $query)->get();
        $totalPaid = $all->sum('montant_paye');
        $totalRemaining = $all->sum(function($f){ return ($f->montant_total - ($f->montant_paye ?? 0)); });
        $totalBilled = $all->sum('montant_total');

        return view('facturations.index', compact('facturations','totalPaid','totalRemaining','totalBilled','start','end'));
    }

    public function exportCsv()
    {
        $query = Facturation::with('entree.vehicule','categorie','user');
        $q = request()->input('q');
        $isPlaqueSearch = $q && preg_match('/^[A-Za-z0-9\-]+$/', $q);
        if ($q) {
            $query->whereHas('entree.vehicule', function($qv) use ($q){ $qv->where('plaque','like','%'.$q.'%'); })
                  ->orWhereHas('entree.client', function($qc) use ($q){ $qc->where('nom','like','%'.$q.'%'); })
                  ->orWhereHas('user', function($qu) use ($q){ $qu->where('name','like','%'.$q.'%'); });
        }
        $start = request()->input('start_date');
        $end = request()->input('end_date');
        if (!$isPlaqueSearch) {
            if ($start) $query->whereDate('created_at','>=',$start);
            if ($end) $query->whereDate('created_at','<=',$end);
        }

        $rows = $query->orderBy('created_at','desc')->get();

        $filename = 'facturations_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output','w');
            fputcsv($out, ['ID','Date','Entree','Plaque','Client','Categorie','Total','Paye','Reste','Utilisateur']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->created_at ? $r->created_at->format('Y-m-d H:i') : '',
                    $r->entree_id,
                    $r->entree?->vehicule?->plaque,
                    $r->entree?->client?->nom,
                    $r->categorie?->nom,
                    number_format($r->montant_total,2),
                    number_format($r->montant_paye,2),
                    number_format(($r->montant_total - ($r->montant_paye ?? 0)),2),
                    $r->user?->name,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $query = Facturation::with('entree.vehicule','categorie','user');
        $q = request()->input('q');
        $isPlaqueSearch = $q && preg_match('/^[A-Za-z0-9\-]+$/', $q);
        if ($q) {
            $query->whereHas('entree.vehicule', function($qv) use ($q){ $qv->where('plaque','like','%'.$q.'%'); })
                  ->orWhereHas('entree.client', function($qc) use ($q){ $qc->where('nom','like','%'.$q.'%'); })
                  ->orWhereHas('user', function($qu) use ($q){ $qu->where('name','like','%'.$q.'%'); });
        }
        $start = request()->input('start_date');
        $end = request()->input('end_date');
        if (!$isPlaqueSearch) {
            if ($start) $query->whereDate('created_at','>=',$start);
            if ($end) $query->whereDate('created_at','<=',$end);
        }

        $rows = $query->orderBy('created_at','desc')->get();

        if (class_exists(\Barryvdh\DomPDF\PDF::class) || class_exists(\Barryvdh\DomPDF\Facade::class)) {
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadView('facturations.export_pdf', compact('rows'));
            return $pdf->download('facturations_'.now()->format('Ymd_His').'.pdf');
        }

        return view('facturations.export_pdf', compact('rows'));
    }

    public function create()
    {
        // access
        if (!in_array(auth()->user()->role, ['superadmin'])) {
            $acc = auth()->user()->acces;
            if (!$acc || !$acc->facturation) {
                abort(403,'Unauthorized');
            }
        }
        $categories = Categorie::all();
        // whether current user can apply reductions
        $canReduce = in_array(auth()->user()->role, ['superadmin']) || (auth()->user()->acces && auth()->user()->acces->reduction);
        return view('facturations.create', compact('categories','canReduce'));
    }

    // JSON lookup: find entree by plaque. Prefer open entrée (no sortie), else latest entrée for that plaque
    public function findByPlaque(Request $request)
    {
        $plaque = $request->query('plaque');
        if (empty($plaque)) return response()->json(['found'=>false]);
        $veh = \App\Models\Vehicule::where('plaque', $plaque)->first();
        if (!$veh) return response()->json(['found'=>false]);
        // try open entree first
        $entree = Entree::with('client','vehicule','user')
            ->where('vehicule_id', $veh->id)
            ->whereNull('date_sortie')
            ->latest('date_entree')
            ->first();
        if (!$entree) {
            $entree = Entree::with('client','vehicule','user')
                ->where('vehicule_id', $veh->id)
                ->latest('date_entree')
                ->first();
        }
        if (!$entree) return response()->json(['found'=>false]);

        // compute duration (days/hours/minutes) between entree and sortie (or now)
        $start = $entree->date_entree;
        $end = $entree->date_sortie ?? Carbon::now();
        $diffInMinutes = $end->diffInMinutes($start);
        $days = intdiv($diffInMinutes, 60*24);
        $hours = intdiv($diffInMinutes % (60*24), 60);
        $minutes = $diffInMinutes % 60;

        // try to find an existing facturation for this entree (latest)
        $fact = \App\Models\Facturation::where('entree_id', $entree->id)->latest()->first();

        // build response payload
        $payload = ['found' => true, 'entree' => $entree, 'duration' => ['days'=>$days,'hours'=>$hours,'minutes'=>$minutes]];
        if ($fact) {
            $payload['facturation'] = [
                'id' => $fact->id,
                'montant_paye' => $fact->montant_paye ?? 0,
                'reduction' => $fact->reduction ?? 0,
                'montant_total' => $fact->montant_total ?? 0,
                'user_name' => $fact->user?->name ?? null,
                'date_paiement' => $fact->date_paiement ?? null,
            ];
        }
        // indicate if the entree is closed: prefer explicit `sortie` boolean, fallback to raw date_sortie sentinel
        $rawDateSortie = $entree->getOriginal('date_sortie');
        $entreeClosed = ($entree->sortie ?? false) || ($rawDateSortie && $rawDateSortie !== '0000-00-00 00:00:00');
        $payload['entree_closed'] = $entreeClosed ? true : false;

        return response()->json($payload);
    }

    // JSON: return the latest open entree (no date_sortie) to prefill the create form
    public function latestOpenEntree(Request $request)
    {
        $entree = Entree::with('client','vehicule','user')
            ->whereNull('date_sortie')
            ->latest('date_entree')
            ->first();
        if (!$entree) return response()->json(['found' => false]);

        $start = $entree->date_entree;
        $end = $entree->date_sortie ?? Carbon::now();
        $diffInMinutes = $end->diffInMinutes($start);
        $days = intdiv($diffInMinutes, 60*24);
        $hours = intdiv($diffInMinutes % (60*24), 60);
        $minutes = $diffInMinutes % 60;

        // indicate if the entree is closed: prefer explicit `sortie` boolean, fallback to raw date_sortie sentinel
        $rawDateSortie = $entree->getOriginal('date_sortie');
        $entreeClosed = ($entree->sortie ?? false) || ($rawDateSortie && $rawDateSortie !== '0000-00-00 00:00:00');
        $payload = ['found' => true, 'entree' => $entree, 'duration' => ['days'=>$days,'hours'=>$hours,'minutes'=>$minutes], 'entree_closed' => $entreeClosed ? true : false];
        return response()->json($payload);
    }

    public function show(Facturation $facturation)
    {
        return view('facturations.show', compact('facturation'));
    }

    public function createFromEntree(Request $request)
    {
        // access
        if (!in_array(auth()->user()->role, ['superadmin'])) {
            $acc = auth()->user()->acces;
            if (!$acc || !$acc->facturation) {
                abort(403,'Unauthorized');
            }
        }
        $request->validate([
            'entree_id' => 'required|exists:entrees,id',
            'categorie_id' => 'required|exists:categories,id',
            'reduction' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'numero' => 'nullable|integer|unique:facturations,numero',
        ]);
        $entree = Entree::findOrFail($request->entree_id);
        // If a facture exists for this entree and the vehicle is still inside, allow updating it.
        $existingFact = \App\Models\Facturation::where('entree_id', $entree->id)->latest()->first();
        // treat DB sentinel '0000-00-00 00:00:00' as no sortie; prefer explicit `sortie` boolean when present
        $rawDateSortie = $entree->getOriginal('date_sortie');
        $entreeClosed = ($entree->sortie ?? false) || ($rawDateSortie && $rawDateSortie !== '0000-00-00 00:00:00');
        if ($existingFact && $entreeClosed) {
            // already exited: do not allow creating/updating
            return back()->with('error','Une facture existe déjà pour cette entrée.');
        }
        // Note: do NOT set entree->date_sortie here. Sortie must be performed from the Sorties page.

        // prefer category from entree; fallback to submitted categorie_id
        $cat = null;
        if ($entree->categorie_id) {
            $cat = Categorie::find($entree->categorie_id);
        } elseif ($request->filled('categorie_id')) {
            $cat = Categorie::find($request->categorie_id);
        }
        // compute server-side authoritative facture using model helper
        // determine allowed reduction from request (server-side permission check)
        $reduction = 0;
        if ($request->filled('reduction')) {
            if (in_array(auth()->user()->role, ['superadmin']) || (auth()->user()->acces && auth()->user()->acces->reduction)) {
                $reduction = max(0, floatval($request->reduction));
            }
        }

        if ($existingFact) {
            // update existing facture (vehicle still inside)
            $fact = $existingFact;
            $fact->user_id = auth()->id();
            $fact->reduction = $reduction;
            $fact->setRelation('entree', $entree);
            if ($cat) $fact->setRelation('categorie', $cat);
            $fact->calculateFromEntree();
            // determine paid amount (clamp to montant_total)
            $paye = 0;
            if ($request->filled('montant_paye')) {
                $paye = max(0, floatval($request->input('montant_paye')));
                if ($paye > $fact->montant_total) $paye = $fact->montant_total;
            }
            $fact->montant_paye = $paye;
            $fact->date_paiement = $paye > 0 ? Carbon::now() : null;
            $fact->save();
        } else {
            // build a Facturation instance (not yet persisted), attach relations so calculateFromEntree can use them
            $fact = new Facturation();
            $fact->entree_id = $entree->id;
            $fact->user_id = auth()->id();
            $fact->reduction = $reduction;
            // attach models so calculateFromEntree can resolve category from either facture or entree
            $fact->setRelation('entree', $entree);
            if ($cat) $fact->setRelation('categorie', $cat);

            // calculate amounts/duration server-side
            $fact->calculateFromEntree();

            // determine paid amount (clamp to montant_total)
            $paye = 0;
            if ($request->filled('montant_paye')) {
                $paye = max(0, floatval($request->input('montant_paye')));
                if ($paye > $fact->montant_total) $paye = $fact->montant_total;
            }
            $fact->montant_paye = $paye;
            $fact->date_paiement = $paye > 0 ? Carbon::now() : null;

            // persist facture
            $fact->save();
        }

        // Create basic accounting journal entry: debit client (411000), credit product (parking 701000)
        try {
            $clientCompte = Compte::where('numero','411000')->first();
            $produitCompte = Compte::where('numero','701000')->first();
            if ($clientCompte && $produitCompte) {
                JournalCompte::create([
                    'libelle' => 'Facture #'.$fact->id,
                    'montant' => $fact->montant_total,
                    'date' => Carbon::now()->toDateString(),
                    'compte_debit_id' => $clientCompte->id,
                    'compte_credit_id' => $produitCompte->id,
                ]);
            }
        } catch(\Exception $e) {
            // don't break user flow if accounting fails
            \Log::error('Accounting entry failed for facture '.$fact->id.': '.$e->getMessage());
        }

        return redirect()->route('facturations.show', $fact->id)->with('success','Facturation créée');
    }

    public function print(Facturation $facturation)
    {
        $entreprise = \App\Models\Entreprise::first();
        return view('facturations.print', compact('facturation','entreprise'));
    }

    public function destroy(Facturation $facturation)
    {
        $facturation->delete();
        return redirect()->route('facturations.index')->with('success','Facturation supprimée');
    }
}
