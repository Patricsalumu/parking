<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom','prix_par_24h'];

    public function facturations()
    {
        return $this->hasMany(Facturation::class);
    }
}
