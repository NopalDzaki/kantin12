<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = ['username', 'email', 'password', 'role'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relasi ke tabel Stan
    public function stan()
    {
        return $this->hasOne(Stan::class, 'id_user');
    }

    // Relasi ke tabel Siswa
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'id_user');
    }

    // JWTSubject Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
