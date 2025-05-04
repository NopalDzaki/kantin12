<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailTransaksiTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('detail_transaksi')) {
            Schema::create('detail_transaksi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_transaksi');
                $table->unsignedBigInteger('id_menu');
                $table->integer('qty');
                $table->double('harga_beli');
                $table->timestamps();

                $table->foreign('id_transaksi')->references('id')->on('transaksi')->onDelete('cascade');
                $table->foreign('id_menu')->references('id')->on('menu')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('detail_transaksi');
    }
}

