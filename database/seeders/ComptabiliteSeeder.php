<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classe;
use App\Models\Compte;
use App\Models\JournalCompte;

class ComptabiliteSeeder extends Seeder
{
    public function run()
    {
        // Ensure classes exist (SYSCOHADA common classes)
        $classes = [
            ['numero' => '1', 'nom' => 'Capitaux', 'type' => 'passif'],
            ['numero' => '2', 'nom' => 'Immobilisations', 'type' => 'actif'],
            ['numero' => '3', 'nom' => 'Stocks', 'type' => 'actif'],
            ['numero' => '4', 'nom' => 'Tiers (Clients/Fournisseurs)', 'type' => 'actif'],
            ['numero' => '5', 'nom' => 'Charges', 'type' => 'charge'],
            ['numero' => '6', 'nom' => 'Charges exceptionnelles', 'type' => 'charge'],
            ['numero' => '7', 'nom' => 'Produits', 'type' => 'produit'],
            ['numero' => '8', 'nom' => 'Comptes spéciaux', 'type' => 'divers'],
        ];

        foreach ($classes as $c) {
            Classe::firstOrCreate(['numero' => $c['numero']], $c);
        }

        // Create generic client account (class 4)
        $class4 = Classe::where('numero','4')->first();
        $clientCompte = Compte::firstOrCreate(
            ['numero' => '411000'],
            ['nom' => 'Clients divers','classe_id' => $class4?->id]
        );

        // Create product account 'parking' in class 7
        $class7 = Classe::where('numero','7')->first();
        $produitCompte = Compte::firstOrCreate(
            ['numero' => '701000'],
            ['nom' => 'Parking','classe_id' => $class7?->id]
        );

        // create a default sales journal if not exists
        if (!JournalCompte::where('libelle','VENTES')->exists()) {
            JournalCompte::create(['libelle'=>'VENTES','montant'=>0,'date'=>now(),'compte_debit_id'=>$clientCompte->id,'compte_credit_id'=>$produitCompte->id]);
        }
    }
}
