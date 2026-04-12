<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom','email','telephone','adresse'];

    public function vehicules()
    {
        return $this->hasMany(Vehicule::class);
    }

    public function entrees()
    {
        return $this->hasMany(Entree::class);
    }
}
