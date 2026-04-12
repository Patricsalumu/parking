<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Acces extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id','reduction','antidate','modification','entree','facturation','sortie'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
