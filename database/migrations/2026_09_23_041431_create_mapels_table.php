<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Ditambahkan import DB

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Matikan pengecekan foreign key agar tidak terhalang tabel lain yang terikat
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('mapels');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::create('mapels', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel');
            $table->string('nama_mapel');
            $table->string('kelompok')->default('Wajib');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('mapels');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};