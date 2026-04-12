<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicule extends Model
{
    use SoftDeletes;

    protected $fillable = ['plaque','marque','compagnie','pays','essieux','client_id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function entrees()
    {
        return $this->hasMany(Entree::class);
    }
}
