<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksiTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('transaksi')) {
            Schema::create('transaksi', function (Blueprint $table) {
                $table->id();
                $table->datetime('tanggal');
                $table->unsignedBigInteger('id_stan');
                $table->unsignedBigInteger('id_siswa');
                $table->enum('status', ['belum dikonfirm', 'dimasak', 'diantar', 'sampai']);
                $table->timestamps();

                $table->foreign('id_stan')->references('id')->on('stan')->onDelete('cascade');
                $table->foreign('id_siswa')->references('id')->on('siswa')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
}

