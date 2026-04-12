<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facturation;
use App\Models\Entree;
use App\Models\Categorie;
use Carbon\Carbon;

class FacturationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $facturations = Facturation::with('entree.vehicule','categorie')->latest()->paginate(20);
        return view('facturations.index', compact('facturations'));
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
        $request->validate(['entree_id' => 'required|exists:entrees,id','categorie_id' => 'required|exists:categories,id']);
        $entree = Entree::findOrFail($request->entree_id);
        // If sortie is not set, set it to now automatically before facturation
        if (!$entree->date_sortie) {
            $entree->date_sortie = Carbon::now();
            $entree->save();
        }

        $cat = Categorie::find($request->categorie_id);
        $days = $entree->durationInDays();
        $total = $days * ($cat->prix_par_24h ?? 0);

        $fact = Facturation::create([
            'entree_id' => $entree->id,
            'categorie_id' => $cat->id,
            'montant_total' => $total,
            'montant_paye' => 0,
            'duree' => $days,
            'reduction' => 0,
        ]);

        return redirect()->route('facturations.show', $fact->id)->with('success','Facturation créée');
    }

    public function destroy(Facturation $facturation)
    {
        $facturation->delete();
        return redirect()->route('facturations.index')->with('success','Facturation supprimée');
    }
}
