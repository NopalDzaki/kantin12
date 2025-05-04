<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stan extends Model
{
    protected $table = 'stan';
    public $timestamps = true;

    protected $fillable = ['nama_stan', 'nama_pemilik', 'Telp', 'id_user'];

    // Relasi ke tabel Menu
    public function menu()
    {
        return $this->hasMany(Menu::class, 'id_stan');
    }

    // Relasi ke tabel Diskon
    public function diskon()
    {
        return $this->hasMany(Diskon::class, 'id_stan');
    }

    // Relasi ke tabel Transaksi
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_stan');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
// nopaldzaki