<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_mapels', function (Blueprint $table) {
            $table->id();
            
            // 1. Relasi Sekolah & Guru / Penginput Data (Penting untuk Multi-Sekolah)
            $table->foreignId('sekolah_id')->nullable()->constrained('sekolahs')->onDelete('cascade');
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->onDelete('set null');

            // 2. Relasi Siswa & Kelas
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');

            // 3. Relasi Mapel (Bisa pakai foreignId jika ada tabel mapels, atau string)
            $table->foreignId('mapel_id')->nullable()->constrained('mapels')->onDelete('cascade');
            // Jika belum ada tabel mapel, pakai string dulu seperti bawaanmu:
            // $table->string('mapel');

            $table->date('tanggal');
            $table->unsignedSmallInteger('pertemuan_ke'); // Tipe data angka kecil (1-255)

            // 4. Status Absensi Menggunakan Enum
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alfa'])->default('Hadir');

            $table->text('keterangan')->nullable(); // Catatan tambahan (opsional)
            $table->timestamps();

            // 5. Indexing untuk mempercepat query saat filter/cetak absensi
            $table->index(['kelas_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_mapels');
    }
};