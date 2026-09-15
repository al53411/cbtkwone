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
        Schema::create('tujuan_pembelajarans', function (Blueprint $table) {
            $table->id();

            // Relasi ke sekolah (opsional / opsional multi-tenant)
            $table->foreignId('sekolah_id')
                  ->nullable()
                  ->constrained('sekolahs')
                  ->onDelete('cascade');

            // Relasi ke tabel mapels
            $table->foreignId('mapel_id')
                  ->constrained('mapels')
                  ->onDelete('cascade');

            // Relasi ke tabel kelas (opsional, jika TP dibedakan per tingkat/kelas)
            $table->foreignId('kelas_id')
                  ->nullable()
                  ->constrained('kelas')
                  ->onDelete('cascade');

            // Informasi Kurikulum Merdeka
            $table->string('fase', 10)->nullable();      // Contoh: Fase A, Fase B, Fase C
            $table->string('elemen')->nullable();        // Contoh: Membaca, Menulis, Menyimak
            $table->string('kode_tp', 50)->nullable();   // Contoh: TP.1.1, TP.1.2
            $table->text('deskripsi_tp');                // Isi/uraian dari Tujuan Pembelajaran

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tujuan_pembelajarans');
    }
};