<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    
    protected $casts = [
        'tanggal' => 'datetime',
        'status' => 'string'
    ];
    
    protected $fillable = [
        'id_siswa',
        'id_stan',
        'tanggal',
        'status',
        'total'
    ];

    // Relasi ke tabel DetailTransaksi
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    // Relasi ke Stan
    public function stan()
    {
        return $this->belongsTo(Stan::class, 'id_stan');
    }

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->belongsTo(User::class, 'id_siswa');
    }
}
