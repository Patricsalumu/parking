<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Entree extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id','vehicule_id','client_id','date_entree','date_sortie','observation','qr_code'];

    protected $dates = ['date_entree','date_sortie'];

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

    public function durationInDays()
    {
        if (!$this->date_sortie) return null;
        $start = Carbon::parse($this->date_entree);
        $end = Carbon::parse($this->date_sortie);
        $hours = $end->diffInHours($start);
        $days = (int) ceil($hours / 24);
        return max(1, $days);
    }
}
