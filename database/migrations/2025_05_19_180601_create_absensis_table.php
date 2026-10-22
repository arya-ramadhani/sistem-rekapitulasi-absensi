<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('absensis', function (Blueprint $table) {
        $table->id();
        $table->string('npm');
        $table->string('nama');
        $table->string('mata_kuliah');
        $table->string('kode_mk')->nullable();
        $table->string('dosen');
        $table->date('tanggal');
        $table->string('minggu');
        $table->string('kelas');
        $table->unsignedBigInteger('kelas_id');
        $table->text('pokok_pembahasan')->nullable();
        $table->json('jam_hadir')->nullable();
        $table->integer('sakit')->default(0);
        $table->integer('izin')->default(0);
        $table->integer('alpa')->default(0);
        $table->timestamps();

        $table->foreign('kelas_id')->references('id')->on('kelass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
