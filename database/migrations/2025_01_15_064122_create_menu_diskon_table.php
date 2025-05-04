<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuDiskonTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('menu_diskon')) {
            Schema::create('menu_diskon', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_menu');
                $table->unsignedBigInteger('id_diskon');
                $table->timestamps();

                $table->foreign('id_menu')->references('id')->on('menu')->onDelete('cascade');
                $table->foreign('id_diskon')->references('id')->on('diskon')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('menu_diskon');
    }
}

