<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = [
        'nama_makanan',
        'harga',
        'jenis',
        'foto',
        'deskripsi',
        'id_stan'
    ];

    protected $appends = ['harga_setelah_diskon'];

    public function getHargaSetelahDiskonAttribute()
    {
        $diskonAktif = $this->diskon()
            ->where('tanggal_awal', '<=', now())
            ->where('tanggal_akhir', '>=', now())
            ->first();

        if ($diskonAktif) {
            $potonganHarga = $this->harga * ($diskonAktif->persentase_diskon / 100);
            return $this->harga - $potonganHarga;
        }

        return $this->harga;
    }

    public function diskon()
    {
        return $this->belongsToMany(Diskon::class, 'menu_diskon', 'id_menu', 'id_diskon');
    }

    // Relasi ke tabel Stan
    public function stan()
    {
        return $this->belongsTo(Stan::class, 'id_stan');
    }

    // Relasi ke tabel DetailTransaksi
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_menu');
    }
}
// nopaldzaki