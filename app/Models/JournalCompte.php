<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalCompte extends Model
{
    use SoftDeletes;

    protected $fillable = ['libelle','montant','date','compte_debit_id','compte_credit_id'];

    public function compteDebit()
    {
        return $this->belongsTo(Compte::class, 'compte_debit_id');
    }

    public function compteCredit()
    {
        return $this->belongsTo(Compte::class, 'compte_credit_id');
    }
}
