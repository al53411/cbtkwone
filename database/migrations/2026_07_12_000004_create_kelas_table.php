<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek dulu apakah tabel kelas BELUM ada sebelum dibuat
        if (!Schema::hasTable('kelas')) {
            Schema::create('kelas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sekolah_id')->nullable()->constrained('sekolahs')->onDelete('cascade');
                $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete(); // ID Wali Kelas
                $table->string('nama_kelas'); // Contoh: VII A, X IPA 1
                $table->string('tingkat')->nullable(); // Contoh: 7, 10, X (Tambahan agar sesuai dengan Controller & View)
                $table->string('wali_kelas')->nullable(); // Backup string nama wali kelas
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};