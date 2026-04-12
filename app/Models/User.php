<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'blocked',
        'caisse_compte_id',
    ];

    public function caisseCompte()
    {
        return $this->belongsTo(\App\Models\Compte::class, 'caisse_compte_id');
    }
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relations
    public function acces()
    {
        return $this->hasOne(Acces::class);
    }

    public function entrees()
    {
        return $this->hasMany(Entree::class);
    }

    // Role helpers
    public function isRole($role)
    {
        return $this->role === $role;
    }

    // Backwards-compat accessor for `nom` used in some views
    public function getNomAttribute()
    {
        return $this->attributes['name'] ?? null;
    }
}
