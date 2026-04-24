<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Entree extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id','vehicule_id','client_id','date_entree','date_sortie','sortie_user_id','observation','categorie_id','sortie'];
    protected $dates = ['date_entree','date_sortie'];
    protected $casts = [
        'date_entree' => 'datetime',
        'date_sortie' => 'datetime',
        'sortie' => 'boolean',
        'numero' => 'integer',
    ];

    // Ensure we always return Carbon instances even if DB contains strings
    public function getDateEntreeAttribute($value)
    {
        if (empty($value)) return null;
        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    public function getDateSortieAttribute($value)
    {
        if (empty($value)) return null;
        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class);
    }

    public function facturation()
    {
        return $this->hasOne(Facturation::class);
    }

    public function sortieUser()
    {
        return $this->belongsTo(User::class, 'sortie_user_id');
    }

    public function categorie()
    {
        return $this->belongsTo(\App\Models\Categorie::class, 'categorie_id');
    }

    public function durationInDays()
    {
        if (!$this->date_sortie) return null;
        $start = Carbon::parse($this->date_entree);
        $end = Carbon::parse($this->date_sortie);
        $hours = $end->diffInHours($start);
        $days = (int) ceil($hours / 24);
        return max(1, $days);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->numero)) {
                // get current max and increment
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
