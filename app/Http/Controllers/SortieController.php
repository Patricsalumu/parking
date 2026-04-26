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
        $query = Entree::with('vehicule','client','user','facturation');

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

        $entrees = $query->orderBy('date_entree','desc')->paginate(15);
        $entrees->appends(request()->all());

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
        $entree->load('vehicule','client','user','facturation.categorie');
        $fact = $entree->facturation;
        $canExit = false;
        $blockedDueTime = false;
        $sinceBilled = null;
        $minutesSince = null;
        if ($fact) {
            $total = $fact->montant_total ?? 0;
            $paye = $fact->montant_paye ?? 0;
            $canExit = ($paye >= $total);
            // compute time since last update on the facture
            $since = \Carbon\Carbon::parse($fact->updated_at ?? now())->diff(Carbon::now());
            $sinceBilled = [
                'days' => $since->d,
                'hours' => $since->h,
                'minutes' => $since->i,
            ];
            // block exit if more than 60 minutes passed since last facture update
            $minutesSince = Carbon::now()->diffInMinutes($fact->updated_at ?? now());
            if ($minutesSince > 60) {
                $blockedDueTime = true;
                $canExit = false;
            }
        }
        // Return fragment for AJAX requests (modal load). If opened directly, render full page.
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('sorties.show', compact('entree','fact','canExit','sinceBilled','blockedDueTime','minutesSince'));
        }
        return view('sorties.full', compact('entree','fact','canExit','sinceBilled','blockedDueTime','minutesSince'));
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

        // enforce 1 hour window since last facturation update
        $minutesSince = Carbon::now()->diffInMinutes($fact->updated_at ?? now());
        if ($minutesSince > 60) {
            return back()->with('error','Impossible de faire la sortie: la facture a été modifiée il y a plus d\'une heure. Veuillez refacturer avant la sortie.');
        }

        $entree->date_sortie = Carbon::now();
        $entree->sortie_user_id = auth()->id();
        // mark as exited
        $entree->sortie = 1;
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

    /**
     * Apurer: touch the related facturation so the 1-hour block is cleared.
     */
    public function apurer(Request $request, Entree $entree)
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
            return back()->with('error','Facture introuvable pour cette entrée.');
        }

        // Register sortie using the facture's updated_at (antidate the sortie)
        $dateSortie = $fact->updated_at ?? Carbon::now();
        $entree->date_sortie = Carbon::parse($dateSortie);
        $entree->sortie_user_id = auth()->id();
        $entree->sortie = 1;
        $entree->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'entree_id' => $entree->id,
                'date_sortie' => $entree->date_sortie->format('Y-m-d H:i'),
                'sinceBilled' => [
                    'days' => 0,
                    'hours' => 0,
                    'minutes' => 0,
                ],
                'message' => 'Sortie enregistrée (antidatée) à la date de la facture.'
            ]);
        }

        return back()->with('success','Sortie enregistrée (antidatée) à la date de la facture.');
    }
}
