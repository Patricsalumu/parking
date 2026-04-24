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
        // search by plaque or client name
        $q = request()->input('q');

        // compute aggregates in DB for performance
        $vehQ = Vehicule::query()
            ->select('vehicules.*')
            ->selectSub(function($qsub){
                $qsub->from('entrees')
                  ->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_total),0)')
                  ->whereColumn('entrees.vehicule_id','vehicules.id');
            }, 'total_billed')
            ->selectSub(function($qsub){
                $qsub->from('entrees')
                  ->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_paye),0)')
                  ->whereColumn('entrees.vehicule_id','vehicules.id');
            }, 'total_paid')
            ->withCount('entrees')
            ->with('client');

        if ($q) {
            $vehQ->where(function($w) use ($q){
                $w->where('plaque','like','%'.$q.'%')
                  ->orWhereHas('client', function($qc) use ($q){ $qc->where('nom','like','%'.$q.'%'); });
            });
        }

        $vehicules = $vehQ->latest()->paginate(15);
        $vehicules->appends(request()->only('q'));

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

    public function show(Vehicule $vehicule)
    {
        $vehicule->load(['client','entrees.facturation']);
        $facturations = \App\Models\Facturation::whereHas('entree', function($q) use ($vehicule){
            $q->where('vehicule_id', $vehicule->id);
        })->with('entree.client')->latest()->get();
        return view('vehicules.show', compact('vehicule','facturations'));
    }

    public function exportCsv()
    {
        $rows = Vehicule::query()
            ->select('vehicules.*')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_total),0)')
                  ->whereColumn('entrees.vehicule_id','vehicules.id');
            }, 'total_billed')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_paye),0)')
                  ->whereColumn('entrees.vehicule_id','vehicules.id');
            }, 'total_paid')
            ->withCount('entrees')
            ->with('client')
            ->orderBy('vehicules.id')
            ->get();

        $filename = 'vehicules_'.now()->format('Ymd_His').'.csv';
        $headers = ['Content-Type'=>'text/csv','Content-Disposition'=>"attachment; filename=\"{$filename}\""];
        $callback = function() use ($rows) {
            $out = fopen('php://output','w');
            fputcsv($out, ['ID','Plaque','Client','#Entrées','Total facturé','Total payé','Reste']);
            foreach($rows as $r){
                $totalBilled = $r->total_billed ?? 0;
                $totalPaid = $r->total_paid ?? 0;
                $remaining = $totalBilled - $totalPaid;
                fputcsv($out, [
                    $r->id,
                    $r->plaque,
                    $r->client?->nom,
                    $r->entrees_count ?? 0,
                    number_format($totalBilled,2),
                    number_format($totalPaid,2),
                    number_format($remaining,2),
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback,200,$headers);
    }

    public function exportPdf()
    {
        $rows = Vehicule::query()
            ->select('vehicules.*')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_total),0)')
                  ->whereColumn('entrees.vehicule_id','vehicules.id');
            }, 'total_billed')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_paye),0)')
                  ->whereColumn('entrees.vehicule_id','vehicules.id');
            }, 'total_paid')
            ->withCount('entrees')
            ->with('client')
            ->orderBy('vehicules.id')
            ->get();

        if (class_exists(\Barryvdh\DomPDF\PDF::class) || class_exists(\Barryvdh\DomPDF\Facade::class)) {
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadView('vehicules.export_pdf', compact('rows'));
            return $pdf->download('vehicules_'.now()->format('Ymd_His').'.pdf');
        }
        return view('vehicules.export_pdf', compact('rows'));
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
