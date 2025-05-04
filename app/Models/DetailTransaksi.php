<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    
    protected $casts = [
        'harga_beli' => 'decimal:2',
        'qty' => 'integer'
    ];
    
    protected $fillable = [
        'id_transaksi',
        'id_menu',
        'qty',
        'harga_beli',
        'subtotal',
        'diskon_id'
    ];

    protected $appends = ['total_harga', 'total_harga_setelah_diskon'];

    public function getTotalHargaAttribute()
    {
        return $this->qty * $this->harga_beli;
    }

    public function getTotalHargaSetelahDiskonAttribute()
    {
        if ($this->menu && $this->menu->harga_setelah_diskon) {
            return $this->qty * $this->menu->harga_setelah_diskon;
        }
        return $this->total_harga;
    }

    // Relasi ke Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    // Relasi ke Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    public function diskon()
    {
        return $this->belongsTo(Diskon::class, 'diskon_id');
    }
}
