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
    // Explicit lookup route must come before the resource route so it is not
    // captured by the resource's `vehicules/{vehicule}` pattern.
    Route::get('vehicules/find-by-plaque', [VehiculeController::class,'findByPlaque'])->name('vehicules.findByPlaque');
    Route::resource('vehicules', VehiculeController::class);
    // Entrées resource and exports
    Route::get('entrees/export/csv', [EntreeController::class,'exportCsv'])->name('entrees.export.csv');
    Route::get('entrees/export/pdf', [EntreeController::class,'exportPdf'])->name('entrees.export.pdf');
    Route::resource('entrees', EntreeController::class);
    Route::get('entrees/{entree}/print', [EntreeController::class,'print'])->name('entrees.print');
    // Sorties (separate views for exiting vehicles)
    Route::get('sorties', [\App\Http\Controllers\SortieController::class,'index'])->name('sorties.index');
    Route::get('sorties/{entree}', [\App\Http\Controllers\SortieController::class,'show'])->name('sorties.show');
    Route::put('sorties/{entree}', [\App\Http\Controllers\SortieController::class,'update'])->name('sorties.update');
    Route::get('facturations', [FacturationController::class,'index'])->name('facturations.index');
    Route::get('facturations/export/csv', [FacturationController::class,'exportCsv'])->name('facturations.export.csv');
    Route::get('facturations/export/pdf', [FacturationController::class,'exportPdf'])->name('facturations.export.pdf');
    Route::get('facturations/create', [FacturationController::class,'create'])->name('facturations.create');
    Route::get('facturations/find-by-plaque', [FacturationController::class,'findByPlaque'])->name('facturations.findByPlaque');
    Route::get('facturations/{facturation}', [FacturationController::class,'show'])->name('facturations.show');
    Route::get('facturations/{facturation}/print', [FacturationController::class,'print'])->name('facturations.print');
    Route::post('facturations/create-from-entree', [FacturationController::class,'createFromEntree'])->name('facturations.createFromEntree');

    Route::resource('categories', CategorieController::class);
    Route::get('paiements', [PaiementController::class,'index'])->name('paiements.index');
    Route::get('paiements/create/{facturation}', [PaiementController::class,'create'])->name('paiements.create');
    Route::post('paiements', [PaiementController::class,'store'])->name('paiements.store');

    Route::resource('users', UserController::class)->except(['show']);
    // settings
    Route::get('settings/entreprise', [\App\Http\Controllers\SettingsController::class,'entreprise'])->name('settings.entreprise');
    Route::post('settings/entreprise', [\App\Http\Controllers\SettingsController::class,'saveEntreprise'])->name('settings.entreprise.save');
    Route::get('settings/classes', [\App\Http\Controllers\SettingsController::class,'classes'])->name('settings.classes');
    Route::get('settings/comptes', [\App\Http\Controllers\SettingsController::class,'comptes'])->name('settings.comptes');
    // classes & comptes resources
    Route::resource('classes', \App\Http\Controllers\ClasseController::class);
    Route::resource('comptes', \App\Http\Controllers\CompteController::class);
    Route::resource('journal_comptes', \App\Http\Controllers\JournalCompteController::class)->only(['index','show']);
});
