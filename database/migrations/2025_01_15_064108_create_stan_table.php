<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    if (!Schema::hasTable('stan')) {
        Schema::create('stan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_stan')->nullable();
            $table->string('nama_pemilik')->nullable();
            $table->string('Telp', 20)->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->timestamps();
        });
    }
}

public function down()
{
    Schema::dropIfExists('stan');
}

};
