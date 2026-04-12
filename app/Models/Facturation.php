<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Facturation extends Model
{
    use SoftDeletes;

    protected $fillable = ['entree_id','categorie_id','montant_total','montant_paye','duree','reduction','date_paiement'];

    protected $dates = ['date_paiement'];

    public function entree()
    {
        return $this->belongsTo(Entree::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function calculateFromEntree()
    {
        $entree = $this->entree;
        if (!$entree || !$entree->date_sortie) return null;
        $days = $entree->durationInDays();
        $price = $this->categorie ? $this->categorie->prix_par_24h : 0;
        $total = $days * $price;
        $this->duree = $days;
        $this->montant_total = $total - ($this->reduction ?? 0);
        return $this;
    }
}
