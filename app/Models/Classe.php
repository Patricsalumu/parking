<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classe extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom','numero','type'];

    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}
