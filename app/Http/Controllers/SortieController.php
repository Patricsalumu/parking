<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entree;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SortieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // show all entries (exited or not) for the given date range (default today)
        $query = Entree::with('vehicule','client','user','facturation','categorie');

        if ($q = request()->input('q')) {
            $query->whereHas('vehicule', function($qv) use ($q){
                $qv->where('plaque','like','%'.$q.'%');
            })->orWhereHas('client', function($qc) use ($q){
                $qc->where('nom','like','%'.$q.'%');
            })->orWhereHas('user', function($qu) use ($q){
                $qu->where('name','like','%'.$q.'%');
            });
        }

        $start = request()->input('start_date', now()->format('Y-m-d'));
        $end = request()->input('end_date', now()->format('Y-m-d'));
        if ($start) $query->whereDate('date_entree', '>=', $start);
        if ($end) $query->whereDate('date_entree', '<=', $end);

        $entrees = $query->orderBy('date_entree','desc')->paginate(20);
        $entrees->appends(request()->all());

        // attach time-since-facturation info for views
        foreach ($entrees as $e) {
            $fact = $e->facturation;
            if ($fact && ($fact->updated_at || $fact->created_at)) {
                $updatedAt = $fact->updated_at ?? $fact->created_at;
                $minutes = Carbon::now()->diffInMinutes(Carbon::parse($updatedAt));
                $diff = Carbon::now()->diff(Carbon::parse($updatedAt));
                $e->sinceBilled = ['days' => $diff->d, 'hours' => $diff->h, 'minutes' => $diff->i];
                $e->minutesSince = $minutes;
            } else {
                $e->sinceBilled = null;
                $e->minutesSince = null;
            }
        }

        // Counts for state cards (respect selected date range)
        $entriesCount = Entree::whereDate('date_entree', '>=', $start)
            ->whereDate('date_entree', '<=', $end)
            ->count();

        $sortiesCount = Entree::whereNotNull('date_sortie')
            ->whereDate('date_sortie', '>=', $start)
            ->whereDate('date_sortie', '<=', $end)
            ->count();

        // stock: entries that were present during the selected range (entered on/before end and not exited before start)
        $stockCount = Entree::whereDate('date_entree', '<=', $end)
            ->where(function($q) use ($start) {
                $q->whereNull('date_sortie')
                  ->orWhereDate('date_sortie', '>=', $start);
            })->count();

        return view('sorties.index', compact('entrees','start','end','entriesCount','sortiesCount','stockCount'));
    }

    public function show(Request $request, Entree $entree)
    {
        $entree->load('vehicule','client','user','facturation.categorie','categorie');
        $fact = $entree->facturation;
        $canExit = false;
        if ($fact) {
            $total = $fact->montant_total ?? 0;
            $paye = $fact->montant_paye ?? 0;
            $canExit = ($paye >= $total);
        }
        // compute time since billing (used to allow immediate sortie after apurement)
        $sinceBilled = null;
        $minutesSince = null;
        if ($fact && ($fact->updated_at || $fact->created_at)) {
            $updatedAt = $fact->updated_at ?? $fact->created_at;
            $minutesSince = Carbon::now()->diffInMinutes(Carbon::parse($updatedAt));
            $diff = Carbon::now()->diff(Carbon::parse($updatedAt));
            $sinceBilled = ['days' => $diff->d, 'hours' => $diff->h, 'minutes' => $diff->i];
        }
        // Return fragment for AJAX requests (modal load). If opened directly, render full page.
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('sorties.show', compact('entree','fact','canExit','sinceBilled','minutesSince'));
        }
        return view('sorties.full', compact('entree','fact','canExit','sinceBilled','minutesSince'));
    }

    public function apurer(Request $request, Entree $entree)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
        \Log::info('Apurer called', ['entree_id' => $entree->id ?? null, 'user_id' => auth()->id() ?? null, 'isAjax' => $isAjax]);

        // access
        if (!in_array(auth()->user()->role, ['superadmin'])) {
            $acc = auth()->user()->acces;
            if (!$acc || !$acc->facturation) {
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }
                abort(403,'Unauthorized');
            }
        }

        $entree->load('facturation');
        $fact = $entree->facturation;
        if (!$fact) {
            if ($isAjax) return response()->json(['success' => false, 'message' => 'Aucune facture associée à cette entrée.'], 404);
            return back()->with('error','Aucune facture associée à cette entrée.');
        }

        \Log::info('Apurer: fact found', ['fact_id' => $fact->id, 'fact_updated_at' => $fact->updated_at]);

        // Set sortie on entree using facturation updated_at (apurement backdates sortie)
        try {
            $dateForSortie = $fact->updated_at ? Carbon::parse($fact->updated_at) : Carbon::now();
            $entree->date_sortie = $dateForSortie;
            $entree->sortie_user_id = auth()->id();
            $entree->sortie = 1;
            $saved = $entree->save();
            \Log::info('Entree updated during apurer', ['entree_id' => $entree->id, 'saved' => $saved, 'date_sortie' => $entree->date_sortie, 'sortie' => $entree->sortie]);
        } catch (\Exception $e) {
            \Log::error('Failed to set entree sortie during apurer for entree '.$entree->id.': '.$e->getMessage());
            if ($isAjax) return response()->json(['success' => false, 'message' => 'Erreur interne lors de la mise à jour de l\'entrée.'], 500);
            return back()->with('error','Erreur interne lors de la mise à jour de l\'entrée.');
        }

        // prepare response payload
        $entree->load('vehicule','sortieUser');
        $ds = $entree->date_sortie ? Carbon::parse($entree->date_sortie)->format('Y-m-d H:i') : null;
        $since = null;
        if ($fact->updated_at) {
            $diff = Carbon::now()->diff(Carbon::parse($fact->updated_at));
            $since = $diff->d.'j '.$diff->h.'h '.$diff->i.'m';
        }

        if ($isAjax) {
            \Log::info('Apurer success response', ['entree_id' => $entree->id]);
            return response()->json([
                'success' => true,
                'message' => 'Facture apurée',
                'entree' => [
                    'id' => $entree->id,
                    'plaque' => $entree->vehicule?->plaque ?? null,
                    'date_sortie' => $ds,
                    'sortie_user' => $entree->sortieUser?->name ?? auth()->user()->name,
                    'sinceBilled' => $since,
                ]
            ]);
        }

        return back()->with('success','Facture apurée');
    }

    public function update(Request $request, Entree $entree)
    {
        // check access
        if (!in_array(auth()->user()->role, ['superadmin'])) {
            $acc = auth()->user()->acces;
            if (!$acc || !$acc->sortie) {
                abort(403,'Unauthorized');
            }
        }

        $entree->load('facturation');
        $fact = $entree->facturation;
        if (!$fact) {
            return back()->with('error','Impossible de faire la sortie: facture introuvable pour cette entrée.');
        }
        $total = $fact->montant_total ?? 0;
        $paye = $fact->montant_paye ?? 0;
        if ($paye < $total) {
            return back()->with('error','Impossible de faire la sortie: la facture n\'est pas encore apurée.');
        }
        // require payment date to exist (paid and recorded)
        if (empty($fact->date_paiement)) {
            return back()->with('error','Impossible de faire la sortie: la date de paiement n\'est pas enregistrée.');
        }

        $entree->date_sortie = Carbon::now();
        $entree->sortie_user_id = auth()->id();
        $entree->save();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'date_sortie' => $entree->date_sortie->toDateTimeString(),
                'sortie_user' => auth()->user()->name,
            ]);
        }
        return redirect()->route('sorties.index')->with('success','Sortie enregistrée');
    }
}
