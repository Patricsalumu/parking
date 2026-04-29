<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

    
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehiculeController;
use App\Http\Controllers\EntreeController;
use App\Http\Controllers\FacturationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\UserController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::get('clients/export/csv', [ClientController::class,'exportCsv'])->name('clients.export.csv');
    Route::get('clients/export/pdf', [ClientController::class,'exportPdf'])->name('clients.export.pdf');
    // Explicit lookup route must come before the resource route so it is not
    // captured by the resource's `vehicules/{vehicule}` pattern.
    Route::get('vehicules/find-by-plaque', [VehiculeController::class,'findByPlaque'])->name('vehicules.findByPlaque');
    Route::get('vehicules/search-plaques', [VehiculeController::class,'searchPlaques'])->name('vehicules.searchPlaques');
    Route::resource('vehicules', VehiculeController::class);
    Route::get('vehicules/export/csv', [VehiculeController::class,'exportCsv'])->name('vehicules.export.csv');
    Route::get('vehicules/export/pdf', [VehiculeController::class,'exportPdf'])->name('vehicules.export.pdf');
    // Entrées resource and exports
    Route::get('entrees/export/csv', [EntreeController::class,'exportCsv'])->name('entrees.export.csv');
    Route::get('entrees/export/pdf', [EntreeController::class,'exportPdf'])->name('entrees.export.pdf');
    Route::resource('entrees', EntreeController::class);
    Route::get('entrees/{entree}/print', [EntreeController::class,'print'])->name('entrees.print');
    // Sorties (separate views for exiting vehicles)
    Route::get('sorties', [\App\Http\Controllers\SortieController::class,'index'])->name('sorties.index');
    Route::get('sorties/{entree}', [\App\Http\Controllers\SortieController::class,'show'])->name('sorties.show');
    Route::put('sorties/{entree}', [\App\Http\Controllers\SortieController::class,'update'])->name('sorties.update');
    Route::post('sorties/{entree}/apurer', [\App\Http\Controllers\SortieController::class,'apurer'])->name('sorties.apurer');
    Route::get('facturations', [FacturationController::class,'index'])->name('facturations.index');
    Route::get('facturations/export/csv', [FacturationController::class,'exportCsv'])->name('facturations.export.csv');
    Route::get('facturations/export/pdf', [FacturationController::class,'exportPdf'])->name('facturations.export.pdf');
    Route::get('facturations/create', [FacturationController::class,'create'])->name('facturations.create');
    Route::get('facturations/latest-open-entree', [FacturationController::class,'latestOpenEntree'])->name('facturations.latestOpenEntree');
    Route::get('facturations/find-by-plaque', [FacturationController::class,'findByPlaque'])->name('facturations.findByPlaque');
    Route::get('facturations/{facturation}', [FacturationController::class,'show'])->name('facturations.show');
    Route::get('facturations/{facturation}/print', [FacturationController::class,'print'])->name('facturations.print');
    Route::post('facturations/create-from-entree', [FacturationController::class,'createFromEntree'])->name('facturations.createFromEntree');

    Route::resource('categories', CategorieController::class);
    Route::get('paiements', [PaiementController::class,'index'])->name('paiements.index');
    Route::get('stocks-physique', [\App\Http\Controllers\StockPhysiqueController::class,'index'])->name('stocks_physique.index');
    Route::get('caisse', [\App\Http\Controllers\CaisseController::class,'index'])->name('caisse.index');
    Route::get('caisse/export/csv', [\App\Http\Controllers\CaisseController::class,'exportCsv'])->name('caisse.export.csv');
    Route::get('caisse/export/pdf', [\App\Http\Controllers\CaisseController::class,'exportPdf'])->name('caisse.export.pdf');
    Route::post('caisse/sortie', [\App\Http\Controllers\CaisseController::class,'storeSortie'])->name('caisse.sortie');
    Route::get('paiements/create/{facturation}', [PaiementController::class,'create'])->name('paiements.create');
    Route::post('paiements', [PaiementController::class,'store'])->name('paiements.store');

    Route::resource('users', UserController::class)->except(['show']);
    // settings
    Route::get('settings/entreprise', [\App\Http\Controllers\SettingsController::class,'entreprise'])->name('settings.entreprise');
    Route::post('settings/entreprise', [\App\Http\Controllers\SettingsController::class,'saveEntreprise'])->name('settings.entreprise.save');
    Route::get('settings/classes', [\App\Http\Controllers\SettingsController::class,'classes'])->name('settings.classes');
    Route::get('settings/comptes', [\App\Http\Controllers\SettingsController::class,'comptes'])->name('settings.comptes');
    // classes & comptes resources
    // Use 'classe' as the route parameter name so it matches controller method signatures
    Route::resource('classes', \App\Http\Controllers\ClasseController::class)->parameters(['classes' => 'classe']);
    Route::resource('comptes', \App\Http\Controllers\CompteController::class);
    // Grand livre and accounting reports (must come before resource route to avoid collision)
    Route::get('journal_comptes/grand-livre', [\App\Http\Controllers\JournalCompteController::class,'grandLivreIndex'])->name('journal_comptes.grand_index');
    Route::get('journal_comptes/grand-livre/{compte}', [\App\Http\Controllers\JournalCompteController::class,'grandLivreShow'])->name('journal_comptes.grand_show');
    Route::get('journal_comptes/balances', [\App\Http\Controllers\JournalCompteController::class,'balances'])->name('journal_comptes.balances');
    Route::get('journal_comptes/compte-resultat', [\App\Http\Controllers\JournalCompteController::class,'compteResultat'])->name('journal_comptes.compte_resultat');
    Route::get('journal_comptes/bilan', [\App\Http\Controllers\JournalCompteController::class,'bilan'])->name('journal_comptes.bilan');
    Route::post('journal_comptes/{journal_compte}/annuler', [\App\Http\Controllers\JournalCompteController::class,'annuler'])->name('journal_comptes.annuler');
    Route::get('journal_comptes/search-comptes', [\App\Http\Controllers\JournalCompteController::class,'searchComptes'])->name('journal_comptes.search_comptes');
    Route::get('journal_comptes/create', [\App\Http\Controllers\JournalCompteController::class,'create'])->name('journal_comptes.create');
    Route::post('journal_comptes', [\App\Http\Controllers\JournalCompteController::class,'store'])->name('journal_comptes.store');
    Route::resource('journal_comptes', \App\Http\Controllers\JournalCompteController::class)->only(['index','show']);
});

// Offline fallback used by service worker
Route::get('/offline', function(){ return response()->view('offline'); })->name('offline');
