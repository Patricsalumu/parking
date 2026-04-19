<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
{
    use SoftDeletes;

    protected $fillable = ['facturation_id','montant','date_paiement','mode','note','user_id'];

    protected $dates = ['date_paiement'];

    public function getDatePaiementAttribute($value)
    {
        if (empty($value)) return null;
        // parse stored timestamps as UTC
        return $value instanceof \Carbon\Carbon ? $value->copy()->setTimezone('UTC') : \Carbon\Carbon::parse($value, 'UTC');
    }

    public function facturation()
    {
        return $this->belongsTo(Facturation::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
