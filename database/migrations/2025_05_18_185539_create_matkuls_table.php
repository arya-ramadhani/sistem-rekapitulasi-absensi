<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatkulsTable extends Migration
{
    public function up()
    {
        Schema::create('matkuls', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->integer('sks_teori');
            $table->integer('sks_praktikum');
            $table->integer('jumlah_sks');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('matkuls');
    }
}
