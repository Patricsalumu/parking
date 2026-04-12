<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
{
    use SoftDeletes;

    protected $fillable = ['facturation_id','montant','date_paiement','mode','note'];

    protected $dates = ['date_paiement'];

    public function facturation()
    {
        return $this->belongsTo(Facturation::class);
    }
}
