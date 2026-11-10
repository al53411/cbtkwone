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
        // 1. Tabel untuk menyimpan jawaban per butir soal (Auto-Save)
        Schema::create('jawaban_siswas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('ujian_id');
            $table->unsignedBigInteger('soal_id');
            $table->string('jawaban', 10)->nullable(); // Pilihan siswa: A, B, C, D, atau E
            $table->tinyInteger('is_benar')->default(0); // 1 = Benar, 0 = Salah
            $table->timestamps();

            // Indeks untuk mempercepat pencarian data
            $table->index(['siswa_id', 'ujian_id', 'soal_id']);
        });

        // 2. Tabel untuk merekap nilai akhir setelah ujian selesai
        Schema::create('nilai_ujians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('ujian_id');
            $table->integer('jumlah_benar')->default(0);
            $table->integer('jumlah_salah')->default(0);
            $table->integer('tidak_dijawab')->default(0);
            $table->decimal('nilai', 5, 2)->default(0.00); // Format nilai skala 100 (contoh: 85.50)
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('status', 20)->default('selesai');
            $table->timestamps();

            // Indeks relasi siswa & ujian
            $table->index(['siswa_id', 'ujian_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('jawaban_siswas');
        Schema::dropIfExists('nilai_ujians');
    }
};
