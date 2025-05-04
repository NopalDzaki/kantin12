<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiskonTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('diskon')) {
            Schema::create('diskon', function (Blueprint $table) {
                $table->id();
                $table->string('nama_diskon');
                $table->double('persentase_diskon');
                $table->datetime('tanggal_awal');
                $table->datetime('tanggal_akhir');
                $table->unsignedBigInteger('id_stan');
                $table->timestamps();

                $table->foreign('id_stan')->references('id')->on('stan')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('diskon');
    }
}

