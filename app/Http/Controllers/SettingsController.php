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
        // If the PHP fileinfo extension is not available on the host, the
        // 'image' validation rule may trigger a fatal error (finfo class missing).
        // In that case fall back to 'mimes' rules which validate by extension.
        $logoRule = 'nullable|image|max:2048';
        $bgRule = 'nullable|image|max:4096';
        // Favicons can be PNG or ICO; avoid `image`/`dimensions` rules which may
        // fail for .ico files on some hosts. Validate by mime/extension + size.
        $favRule = 'nullable|mimes:png,ico|max:512';
        if (!class_exists('finfo')) {
            $logoRule = 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048';
            $bgRule = 'nullable|mimes:jpg,jpeg,png,webp|max:4096';
            $favRule = 'nullable|mimes:png,ico|max:512';
        }
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
            'logo' => $logoRule,
            'background' => $bgRule,
            'favicon' => $favRule,
            'timezone' => 'nullable|string|in:Africa/Kinshasa,Africa/Lubumbashi',
        ]);
        $entreprise = Entreprise::first();
        if ($request->hasFile('logo')) {
            // delete old
            if ($entreprise && $entreprise->logo) {
                Storage::disk('public')->delete($entreprise->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('background')) {
            if ($entreprise && $entreprise->background) {
                Storage::disk('public')->delete($entreprise->background);
            }
            $data['background'] = $request->file('background')->store('assets', 'public');
        }
        if ($request->hasFile('favicon')) {
            if ($entreprise && $entreprise->favicon) {
                Storage::disk('public')->delete($entreprise->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('assets', 'public');
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
