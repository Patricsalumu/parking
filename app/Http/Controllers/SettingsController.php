<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entreprise;
use Illuminate\Support\Facades\Storage;
use App\Models\Classe;
use App\Models\Compte;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function entreprise()
    {
        $entreprise = Entreprise::first();
        return view('settings.entreprise', compact('entreprise'));
    }

    public function saveEntreprise(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'devise' => 'nullable|string|in:$,Fc',
            'taux_change' => 'nullable|numeric',
            'slogan' => 'nullable|string',
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string',
            'rccm' => 'nullable|string',
            'id_nat' => 'nullable|string',
            'num_impot' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);
        $entreprise = Entreprise::first();
        if ($request->hasFile('logo')) {
            // delete old
            if ($entreprise && $entreprise->logo) {
                Storage::disk('public')->delete($entreprise->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }
        $entreprise = Entreprise::updateOrCreate(['id'=>1], $data);
        return redirect()->route('settings.entreprise')->with('success','Entreprise saved');
    }

    public function classes()
    {
        $classes = Classe::paginate(20);
        return view('settings.classes', compact('classes'));
    }

    public function comptes()
    {
        $comptes = Compte::with('classe')->paginate(20);
        return view('settings.comptes', compact('comptes'));
    }
}
