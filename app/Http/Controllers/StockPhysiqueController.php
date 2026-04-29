<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entree;

class StockPhysiqueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $q = request()->input('q');

        $query = Entree::with('vehicule', 'client', 'user')
            ->whereNull('date_sortie')
            ->where(function ($sq) {
                $sq->where('sortie', false)->orWhereNull('sortie');
            });

        if ($q) {
            $query->where(function ($sq) use ($q) {
                $sq->whereHas('vehicule', function ($qv) use ($q) {
                    $qv->where('plaque', 'like', '%' . $q . '%');
                })->orWhereHas('client', function ($qc) use ($q) {
                    $qc->where('nom', 'like', '%' . $q . '%');
                });
            });
        }

        $entrees = $query->orderBy('date_entree', 'asc')->paginate(50);
        $entrees->appends(request()->all());

        return view('stocks_physique.index', compact('entrees'));
    }
}
