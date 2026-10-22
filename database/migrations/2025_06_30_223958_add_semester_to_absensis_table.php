<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSemesterToAbsensisTable extends Migration
{
    public function up()
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('semester')->nullable()->after('kelas_id');
        });
    }

    public function down()
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn('semester');
        });
    }
}