<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diskon extends Model
{
    protected $table = 'diskon';

    protected $casts = [
        'tanggal_awal' => 'datetime',
        'tanggal_akhir' => 'datetime',
        'persentase_diskon' => 'decimal:2',
    ];

    protected $fillable = [
        'nama_diskon',
        'persentase_diskon',
        'tanggal_awal',
        'tanggal_akhir',
        'id_stan',
    ];

    // Relasi ke tabel MenuDiskon
    public function menuDiskon()
    {
        return $this->hasMany(MenuDiskon::class, 'id_diskon');
    }

    // Relasi ke tabel Stan
    public function stan()
    {
        return $this->belongsTo(Stan::class, 'id_stan');
    }

    // Relasi ke tabel Menu
    public function menu()
    {
        return $this->belongsToMany(Menu::class, 'menu_diskon', 'id_diskon', 'id_menu');
    }
}
