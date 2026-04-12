<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entreprise extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom','devise','taux_change','slogan','telephone','logo','rccm','id_nat','num_impot','adresse'];
}
