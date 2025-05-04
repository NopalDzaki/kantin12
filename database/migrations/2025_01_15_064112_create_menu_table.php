<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    public function up()
{
    Schema::create('menu', function (Blueprint $table) {
        $table->id();
        $table->string('nama_makanan', 100);
        $table->decimal('harga', 10, 2);
        $table->enum('jenis', ['makanan', 'minuman']);
        $table->string('foto')->nullable();
        $table->text('deskripsi')->nullable();
        $table->foreignId('id_stan')->constrained('stan')->onDelete('cascade');
        $table->timestamps();
    });
}

    public function down()
    {
        Schema::dropIfExists('menu');
    }
}
