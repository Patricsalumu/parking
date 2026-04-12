<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom','numero','classe_id'];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function journalComptesDebit()
    {
        return $this->hasMany(JournalCompte::class, 'compte_debit_id');
    }

    public function journalComptesCredit()
    {
        return $this->hasMany(JournalCompte::class, 'compte_credit_id');
    }
}
