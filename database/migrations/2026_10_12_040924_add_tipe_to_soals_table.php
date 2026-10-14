<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            if (!Schema::hasColumn('soals', 'mapel_id')) {
                $table->unsignedBigInteger('mapel_id')->nullable();
            }
            if (!Schema::hasColumn('soals', 'kelas_id')) {
                $table->unsignedBigInteger('kelas_id')->nullable();
            }
            if (!Schema::hasColumn('soals', 'kode_soal')) {
                $table->string('kode_soal')->nullable();
            }
            if (!Schema::hasColumn('soals', 'kunci_jawaban')) {
                $table->string('kunci_jawaban')->nullable();
            }
            if (!Schema::hasColumn('soals', 'tipe')) {
                $table->string('tipe')->default('pg');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            //
        });
    }
};
