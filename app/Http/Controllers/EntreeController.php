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
        $query = Entree::with('vehicule','client','user');
        // search query for plaque, client name or user name
        $q = request()->input('q');
        // consider it a plaque search when the query is a single token of letters/numbers/hyphen (no spaces)
        $isPlaqueSearch = $q && preg_match('/^[A-Za-z0-9\-]+$/', $q);
        if ($q) {
            $query->whereHas('vehicule', function($qv) use ($q){
                $qv->where('plaque','like','%'.$q.'%');
            })->orWhereHas('client', function($qc) use ($q){
                $qc->where('nom','like','%'.$q.'%');
            })->orWhereHas('user', function($qu) use ($q){
                $qu->where('name','like','%'.$q.'%');
            });
        }

        // filter by specific client or user ids
        if ($client = request()->input('client_id')) {
            $query->where('client_id', $client);
        }
        if ($user = request()->input('user_id')) {
            $query->where('user_id', $user);
        }

        // date range filter (defaults to today) - skip when doing a plaque search
        $start = request()->input('start_date', now()->format('Y-m-d'));
        $end = request()->input('end_date', now()->format('Y-m-d'));
        if (!$isPlaqueSearch) {
            if ($start) {
                $query->whereDate('date_entree', '>=', $start);
            }
            if ($end) {
                $query->whereDate('date_entree', '<=', $end);
            }
        }

        $entrees = $query->orderBy('numero','asc')->paginate(50);
        $entrees->appends(request()->all());
        $clients = Client::all();
        $users = \App\Models\User::all();
        return view('entrees.index', compact('entrees','clients','users'));
    }

    public function exportCsv()
    {
        $query = Entree::with('vehicule','client','user');
        $q = request()->input('q');
        $isPlaqueSearch = $q && preg_match('/^[A-Za-z0-9\-]+$/', $q);
        if ($q) {
            $query->whereHas('vehicule', function($qv) use ($q){ $qv->where('plaque','like','%'.$q.'%'); })
                  ->orWhereHas('client', function($qc) use ($q){ $qc->where('nom','like','%'.$q.'%'); })
                  ->orWhereHas('user', function($qu) use ($q){ $qu->where('name','like','%'.$q.'%'); });
        }
        if ($client = request()->input('client_id')) $query->where('client_id', $client);
        if ($user = request()->input('user_id')) $query->where('user_id', $user);
        $start = request()->input('start_date', now()->format('Y-m-d'));
        $end = request()->input('end_date', now()->format('Y-m-d'));
        if (!$isPlaqueSearch) {
            if ($start) $query->whereDate('date_entree','>=',$start);
            if ($end) $query->whereDate('date_entree','<=',$end);
        }

        $rows = $query->orderBy('date_entree','desc')->get();

        $filename = 'entrees_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($rows, $start, $end) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Export Date', format_dt(\Carbon\Carbon::now())]);
            fputcsv($out, ['Start Date', $start]);
            fputcsv($out, ['End Date', $end]);
            fputcsv($out, []);
            fputcsv($out, ['ID','Date Entree','Plaque','Compagnie','Client','Utilisateur','Observation']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->date_entree ? format_dt($r->date_entree) : '',
                    $r->vehicule?->plaque,
                    $r->vehicule?->compagnie,
                    $r->client?->nom,
                    $r->user?->name,
                    $r->observation,
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $query = Entree::with('vehicule','client','user');
        $q = request()->input('q');
        $isPlaqueSearch = $q && preg_match('/^[A-Za-z0-9\-]+$/', $q);
        if ($q) {
            $query->whereHas('vehicule', function($qv) use ($q){ $qv->where('plaque','like','%'.$q.'%'); })
                  ->orWhereHas('client', function($qc) use ($q){ $qc->where('nom','like','%'.$q.''); })
                  ->orWhereHas('user', function($qu) use ($q){ $qu->where('name','like','%'.$q.'%'); });
        }
        if ($client = request()->input('client_id')) $query->where('client_id', $client);
        if ($user = request()->input('user_id')) $query->where('user_id', $user);
        $start = request()->input('start_date', now()->format('Y-m-d'));
        $end = request()->input('end_date', now()->format('Y-m-d'));
        if (!$isPlaqueSearch) {
            if ($start) $query->whereDate('date_entree','>=',$start);
            if ($end) $query->whereDate('date_entree','<=',$end);
        }

        $rows = $query->orderBy('date_entree','desc')->get();

        // If DomPDF is installed use it, otherwise return HTML for browser print
        $exportDate = format_dt(\Carbon\Carbon::now());
        if (class_exists(\Barryvdh\DomPDF\PDF::class) || class_exists(\Barryvdh\DomPDF\Facade::class)) {
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadView('entrees.export_pdf', compact('rows','start','end','exportDate'));
            return $pdf->download('entrees_'.now()->format('Ymd_His').'.pdf');
        }
        // fallback: render HTML view and let user print to PDF from browser
        return view('entrees.export_pdf', compact('rows','start','end','exportDate'));
    }

    public function create()
    {
        $clients = Client::all();
        $vehicules = Vehicule::all();
        $categories = \App\Models\Categorie::all();
        $canAntidate = in_array(auth()->user()->role, ['superadmin']) || (auth()->user()->acces && auth()->user()->acces->antidate);
        return view('entrees.create', compact('clients','vehicules','categories','canAntidate'));
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
            'numero' => 'nullable|integer|unique:entrees,numero',
            'client_id' => 'nullable|exists:clients,id',
            'client_nom' => 'nullable|string',
            'vehicule_id' => 'nullable|exists:vehicules,id',
            'plaque' => 'required|string',
            'compagnie' => 'required|string',
            'marque' => 'nullable|string',
            'pays' => 'required|string',
            'essieux' => 'nullable|integer',
            'observation' => 'nullable|string',
            'categorie_id' => 'nullable|exists:categories,id',
            'date_entree_saisie' => 'nullable|date',
        ]);

        $canAntidate = in_array(auth()->user()->role, ['superadmin']) || (auth()->user()->acces && auth()->user()->acces->antidate);

        // create/select client inline
        if (empty($data['client_id']) && !empty($data['client_nom'])) {
            $client = Client::create(['nom'=>$data['client_nom']]);
            $data['client_id'] = $client->id;
        }

        // create or update vehicule by plaque
        $vehicule = Vehicule::where('plaque', $data['plaque'])->first();
        if ($vehicule) {
            // update fields if provided
            $vehicule->update([
                'marque' => $data['marque'] ?? $vehicule->marque,
                'compagnie' => $data['compagnie'] ?? $vehicule->compagnie,
                'pays' => $data['pays'] ?? $vehicule->pays,
                'essieux' => $data['essieux'] ?? $vehicule->essieux,
                'client_id' => $data['client_id'] ?? $vehicule->client_id,
            ]);
        } else {
            $vehicule = Vehicule::create([
                'plaque' => $data['plaque'],
                'marque' => $data['marque'] ?? null,
                'compagnie' => $data['compagnie'] ?? null,
                'pays' => $data['pays'] ?? null,
                'essieux' => $data['essieux'] ?? null,
                'client_id' => $data['client_id'] ?? null,
            ]);
        }
        $data['vehicule_id'] = $vehicule->id;

        // Prevent duplicate open entries: no two entries without a sortie for same vehicle
        $existingOpen = Entree::where('vehicule_id', $vehicule->id)
            ->whereNull('date_sortie')
            ->exists();
        if ($existingOpen) {
            return back()->withInput()->withErrors(['plaque' => 'Une entrée active existe déjà pour cette plaque (pas de date de sortie)']);
        }

        // If user can antidate and supplied a datetime, use it as canonical timestamp.
        $entryDateTime = ($canAntidate && !empty($data['date_entree_saisie']))
            ? Carbon::parse($data['date_entree_saisie'])->utc()
            : Carbon::now()->utc();

        $entree = new Entree();
        $entree->user_id = Auth::id();
        $entree->vehicule_id = $data['vehicule_id'];
        $entree->client_id = $data['client_id'] ?? null;
        $entree->date_entree = $entryDateTime;
        $entree->observation = $data['observation'] ?? null;
        $entree->categorie_id = $data['categorie_id'] ?? null;
        $entree->created_at = $entryDateTime;
        $entree->updated_at = $entryDateTime;
        $entree->save();

        return redirect()->route('entrees.print', $entree)->with('success','Entrée enregistrée');
    }

    public function edit(Entree $entree)
    {
        $clients = Client::all();
        $vehicules = Vehicule::all();
        $categories = \App\Models\Categorie::all();
        return view('entrees.edit', compact('entree','clients','vehicules','categories'));
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
            'numero' => 'nullable|integer|unique:entrees,numero,'.$entree->id,
            'client_id' => 'nullable|exists:clients,id',
            'client_nom' => 'nullable|string',
            'vehicule_id' => 'nullable|exists:vehicules,id',
            'plaque' => 'required|string',
            'compagnie' => 'required|string',
            'marque' => 'nullable|string',
            'pays' => 'required|string',
            'essieux' => 'nullable|integer',
            'observation' => 'nullable|string',
            'categorie_id' => 'nullable|exists:categories,id',
        ]);

        // create/select client inline
        if (empty($data['client_id']) && !empty($data['client_nom'])) {
            $client = Client::create(['nom'=>$data['client_nom']]);
            $data['client_id'] = $client->id;
        }

        // create or update vehicule by plaque
        $vehicule = Vehicule::where('plaque', $data['plaque'])->first();
        if ($vehicule) {
            $vehicule->update([
                'marque' => $data['marque'] ?? $vehicule->marque,
                'compagnie' => $data['compagnie'] ?? $vehicule->compagnie,
                'pays' => $data['pays'] ?? $vehicule->pays,
                'essieux' => $data['essieux'] ?? $vehicule->essieux,
                'client_id' => $data['client_id'] ?? $vehicule->client_id,
            ]);
        } else {
            $vehicule = Vehicule::create([
                'plaque' => $data['plaque'],
                'marque' => $data['marque'] ?? null,
                'compagnie' => $data['compagnie'] ?? null,
                'pays' => $data['pays'] ?? null,
                'essieux' => $data['essieux'] ?? null,
                'client_id' => $data['client_id'] ?? null,
            ]);
        }
        $data['vehicule_id'] = $vehicule->id;

        // Prevent duplicate open entries for this vehicle (exclude current entree)
        $existingOpen = Entree::where('vehicule_id', $vehicule->id)
            ->whereNull('date_sortie')
            ->where('id', '<>', $entree->id)
            ->exists();
        if ($existingOpen) {
            return back()->withInput()->withErrors(['plaque' => 'Une entrée active existe déjà pour cette plaque (pas de date de sortie)']);
        }

        // date_sortie is set automatically at facture time; do not allow manual edit here.
        $entree->vehicule_id = $data['vehicule_id'];
        $entree->client_id = $data['client_id'] ?? $entree->client_id;
        $entree->observation = $data['observation'] ?? $entree->observation;
        $entree->categorie_id = $data['categorie_id'] ?? $entree->categorie_id;
        $entree->save();
        return redirect()->route('entrees.index')->with('success','Entrée mise à jour');
    }

    public function destroy(Entree $entree)
    {
        $entree->delete();
        return redirect()->route('entrees.index')->with('success','Entrée supprimée');
    }

    public function print(Entree $entree)
    {
        $entreprise = \App\Models\Entreprise::first();
        return view('entrees.print', compact('entree','entreprise'));
    }
}
