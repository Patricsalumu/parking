<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Acces;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // try to find a caisse compte to attach to the user if available
        $caisseCompte = null;
        try {
            $caisseCompte = \App\Models\Compte::whereRaw("LOWER(nom) LIKE '%caisse%'")->first();
            if (! $caisseCompte) {
                $caisseCompte = \App\Models\Compte::where('numero','like','53%')->first();
            }
            if (! $caisseCompte) {
                $caisseCompte = \App\Models\Compte::first();
            }
        } catch (\Exception $e) {
            $caisseCompte = null;
        }

        $userData = [
            'name' => 'Super Admin',
            'password' => Hash::make('admin123'),
            'role' => 'superadmin'
        ];
        if ($caisseCompte) {
            $userData['caisse_compte_id'] = $caisseCompte->id;
        }

        $user = User::firstOrCreate([
            'email' => 'patricksalumumboka@gmail.com'
        ], $userData);

        Acces::firstOrCreate(['user_id' => $user->id], [
            'reduction' => true,
            'antidate' => true,
            'modification' => true,
            'entree' => true,
            'facturation' => true,
            'sortie' => true,
        ]);
    }
}
