<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Categorie;

class Facturation extends Model
{
    use SoftDeletes;

    protected $fillable = ['entree_id','user_id','montant_total','montant_paye','duree','reduction','date_paiement'];

    protected $dates = ['date_paiement'];
    protected $casts = [
        'date_paiement' => 'datetime',
        'numero' => 'integer',
    ];

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

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getDatePaiementAttribute($value)
    {
        if (empty($value)) return null;
        return $value instanceof Carbon ? $value->copy()->setTimezone('UTC') : Carbon::parse($value, 'UTC');
    }

    public function calculateFromEntree()
    {
        $entree = $this->entree;
        if (!$entree) return null;
        $start = Carbon::parse($entree->date_entree);
        $end = $entree->date_sortie ? Carbon::parse($entree->date_sortie) : Carbon::now();
        $hours = $end->diffInHours($start);
        // prefer category linked to this facturation, otherwise fetch from entree
        $cat = $this->categorie ?? (isset($entree->categorie_id) ? Categorie::find($entree->categorie_id) : null);
        $price = $cat ? $cat->prix_par_24h : 0;
        $catId = $cat ? $cat->id : null;

        // billing rules:
        // - first 24h = full price
        // - after the first day, every additional full 24h = full price
        // - if the final partial day (beyond full 24h blocks) has <=5h, charge 50% of price,
        //   otherwise charge full price for that last partial day
        if ($hours <= 24) {
            $total = $price;
        } else {
            $remaining = $hours - 24;
            $fullAdditionalDays = intdiv($remaining, 24);
            $remainder = $remaining % 24;
            $total = $price; // first day
            $total += $fullAdditionalDays * $price;
            if ($remainder > 0) {
                if ($catId === 2) {
                    $total += ($remainder <= 5) ? ($price * 0.5) : $price;
                } else {
                    $total += $price;
                }
            }
        }

        // Special rule: category 1 (canter) pays only if they stayed overnight.
        // If entry date == current date, charge 0.
        if ($catId == 1) {
            if ($start->toDateString() === Carbon::now()->toDateString()) {
                $total = 0;
            }
        }

        // store duration in days (as before, for backward compatibility)
        $this->duree = (int) ceil(max(1, $hours) / 24);
        $this->montant_total = max(0, $total - ($this->reduction ?? 0));
        return $this;
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->numero)) {
                $max = (int) DB::table($model->getTable())->max('numero');
                $model->numero = $max + 1;
            }
        });
    }

    public function getNumeroFormattedAttribute()
    {
        if (empty($this->numero)) return null;
        return str_pad((string) $this->numero, 6, '0', STR_PAD_LEFT);
    }
}
