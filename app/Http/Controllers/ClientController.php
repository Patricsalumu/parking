<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin', ['except' => ['index','show']]);
    }

    public function index()
    {
        // search query (name / email / telephone)
        $q = request()->input('q');

        // Use DB-side aggregates (subqueries) to compute billing sums efficiently
        $clientsQ = Client::query()
            ->select('clients.*')
            ->selectSub(function($qsub){
                $qsub->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_total),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_billed')
            ->selectSub(function($qsub){
                $qsub->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_paye),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_paid')
            ->selectSub(function($qsub){
                $qsub->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.reduction),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_reduction')
            ->withCount('entrees')
            ->with('vehicules');

        if ($q) {
            $clientsQ->where(function($w) use ($q){
                $w->where('nom','like','%'.$q.'%')
                  ->orWhere('email','like','%'.$q.'%')
                  ->orWhere('telephone','like','%'.$q.'%');
            });
        }

        $clients = $clientsQ->latest()->paginate(15);
        $clients->appends(request()->only('q'));

        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $client->load(['vehicules','entrees.vehicule','entrees.facturation']);
        $facturations = \App\Models\Facturation::whereHas('entree', function($q) use ($client){
            $q->where('client_id', $client->id);
        })->with('entree.vehicule')->latest()->get();
        return view('clients.show', compact('client','facturations'));
    }

    public function exportCsv()
    {
                $start = request()->input('start_date', null);
                $end = request()->input('end_date', null);

                $rows = Client::query()
            ->select('clients.*')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_total),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_billed')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_paye),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_paid')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.reduction),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_reduction')
            ->withCount('entrees')
            ->orderBy('clients.id')
            ->get();
                if ($start || $end) {
                        $rows = Client::query()
                                ->select('clients.*')
                                ->selectSub(function($q) use ($start, $end){
                                        $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                                            ->selectRaw('COALESCE(SUM(facturations.montant_total),0)')
                                            ->whereColumn('entrees.client_id','clients.id')
                                            ->when($start, function($q2) use ($start){ $q2->whereDate('facturations.created_at','>=',$start); })
                                            ->when($end, function($q2) use ($end){ $q2->whereDate('facturations.created_at','<=',$end); });
                                }, 'total_billed')
                                ->selectSub(function($q) use ($start, $end){
                                        $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                                            ->selectRaw('COALESCE(SUM(facturations.montant_paye),0)')
                                            ->whereColumn('entrees.client_id','clients.id')
                                            ->when($start, function($q2) use ($start){ $q2->whereDate('facturations.created_at','>=',$start); })
                                            ->when($end, function($q2) use ($end){ $q2->whereDate('facturations.created_at','<=',$end); });
                                }, 'total_paid')
                                ->selectSub(function($q) use ($start, $end){
                                        $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                                            ->selectRaw('COALESCE(SUM(facturations.reduction),0)')
                                            ->whereColumn('entrees.client_id','clients.id')
                                            ->when($start, function($q2) use ($start){ $q2->whereDate('facturations.created_at','>=',$start); })
                                            ->when($end, function($q2) use ($end){ $q2->whereDate('facturations.created_at','<=',$end); });
                                }, 'total_reduction')
                                ->withCount('entrees')
                                ->orderBy('clients.id')
                                ->get();
                }

        $filename = 'clients_'.now()->format('Ymd_His').'.csv';
        $headers = ['Content-Type'=>'text/csv','Content-Disposition'=>"attachment; filename=\"{$filename}\""];
        $callback = function() use ($rows) {
            $out = fopen('php://output','w');
            fputcsv($out, ['Export Date', \Carbon\Carbon::now()->format('Y-m-d H:i')]);
            fputcsv($out, ['Start Date', request()->input('start_date')]);
            fputcsv($out, ['End Date', request()->input('end_date')]);
            fputcsv($out, []);
            fputcsv($out, ['ID','Nom','Telephone','#Entrées','Total facturé','Total payé','Total réduction','Reste']);
            foreach($rows as $r) {
                $totalBilled = $r->total_billed ?? 0;
                $totalPaid = $r->total_paid ?? 0;
                $totalReduction = $r->total_reduction ?? 0;
                $remaining = $totalBilled - $totalPaid;
                fputcsv($out, [
                    $r->id,
                    $r->nom,
                    $r->telephone,
                    $r->entrees_count ?? 0,
                    number_format($totalBilled,2),
                    number_format($totalPaid,2),
                    number_format($totalReduction,2),
                    number_format($remaining,2),
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback,200,$headers);
    }

    public function exportPdf()
    {
        $start = request()->input('start_date', null);
        $end = request()->input('end_date', null);

        $rows = Client::query()
            ->select('clients.*')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_total),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_billed')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.montant_paye),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_paid')
            ->selectSub(function($q){
                $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                  ->selectRaw('COALESCE(SUM(facturations.reduction),0)')
                  ->whereColumn('entrees.client_id','clients.id');
            }, 'total_reduction')
            ->withCount('entrees')
            ->orderBy('clients.id')
            ->get();
        if ($start || $end) {
            $rows = Client::query()
                ->select('clients.*')
                ->selectSub(function($q) use ($start, $end){
                    $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                      ->selectRaw('COALESCE(SUM(facturations.montant_total),0)')
                      ->whereColumn('entrees.client_id','clients.id')
                      ->when($start, function($q2) use ($start){ $q2->whereDate('facturations.created_at','>=',$start); })
                      ->when($end, function($q2) use ($end){ $q2->whereDate('facturations.created_at','<=',$end); });
                }, 'total_billed')
                ->selectSub(function($q) use ($start, $end){
                    $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                      ->selectRaw('COALESCE(SUM(facturations.montant_paye),0)')
                      ->whereColumn('entrees.client_id','clients.id')
                      ->when($start, function($q2) use ($start){ $q2->whereDate('facturations.created_at','>=',$start); })
                      ->when($end, function($q2) use ($end){ $q2->whereDate('facturations.created_at','<=',$end); });
                }, 'total_paid')
                ->selectSub(function($q) use ($start, $end){
                    $q->from('entrees')->join('facturations','facturations.entree_id','=','entrees.id')
                      ->selectRaw('COALESCE(SUM(facturations.reduction),0)')
                      ->whereColumn('entrees.client_id','clients.id')
                      ->when($start, function($q2) use ($start){ $q2->whereDate('facturations.created_at','>=',$start); })
                      ->when($end, function($q2) use ($end){ $q2->whereDate('facturations.created_at','<=',$end); });
                }, 'total_reduction')
                ->withCount('entrees')
                ->orderBy('clients.id')
                ->get();
        }

        if (class_exists(\Barryvdh\DomPDF\PDF::class) || class_exists(\Barryvdh\DomPDF\Facade::class)) {
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadView('clients.export_pdf', compact('rows'));
            return $pdf->download('clients_'.now()->format('Ymd_His').'.pdf');
        }
        return view('clients.export_pdf', compact('rows'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'email' => 'nullable|email',
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string',
        ]);
        Client::create($data);
        return redirect()->route('clients.index')->with('success','Client created');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'email' => 'nullable|email',
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string',
        ]);
        $client->update($data);
        return redirect()->route('clients.index')->with('success','Client updated');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success','Client deleted');
    }
}
