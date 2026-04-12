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
        $user = User::firstOrCreate([
            'email' => 'patricksalumumboka@gmail.com'
        ],[
            'name' => 'Super Admin',
            'password' => Hash::make('admin123'),
            'role' => 'superadmin'
        ]);

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
