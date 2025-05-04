<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    
    protected $fillable = [
        'nama_siswa',
        'alamat',
        'telp',
        'id_user',
        'foto'
    ];

    // Relasi ke tabel Transaksi
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_siswa');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
// nopaldzaki