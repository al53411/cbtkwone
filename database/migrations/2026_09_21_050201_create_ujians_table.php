<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ujian');
            $table->unsignedBigInteger('kelas_id');
            $table->foreignId('mapel_id')->constrained('mapels')->onDelete('cascade');
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->integer('durasi'); // dalam menit
            $table->string('token', 6);
            $table->boolean('acak_soal')->default(false);
            $table->boolean('acak_jawaban')->default(false);
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->timestamps();
        });
    }
};