<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuDiskon extends Model
{
    protected $fillable = ['id_menu', 'id_diskon'];

    // Relasi ke Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    // Relasi ke Diskon
    public function diskon()
    {
        return $this->belongsTo(Diskon::class, 'id_diskon');
    }
}
// nopaldzaki