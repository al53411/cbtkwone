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
        Schema::create('capaian_pembelajarans', function (Blueprint $table) {
            $table->id();

            // Multi-tenant (NULL = CP Bawaan/Nasional, Terisi = CP Khusus Sekolah)
            $table->foreignId('sekolah_id')
                  ->nullable()
                  ->constrained('sekolahs')
                  ->onDelete('cascade');

            // Relasi ke Mata Pelajaran
            $table->foreignId('mapel_id')
                  ->constrained('mapels')
                  ->onDelete('cascade');

            // Struktur Kurikulum Merdeka
            $table->string('fase', 20);                  // Contoh: Fase A, Fase B, atau A, B
            $table->string('elemen');                    // Contoh: Menyimak, Membaca dan Memirsa
            $table->text('deskripsi_cp')->nullable();    // Uraian Capaian Pembelajaran

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capaian_pembelajarans');
    }
};