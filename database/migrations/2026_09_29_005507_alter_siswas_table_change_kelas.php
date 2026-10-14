<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan perubahan struktur tabel.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // 1. Hapus foreign key & kolom kelas_id jika ada
            if (Schema::hasColumn('siswas', 'kelas_id')) {
                $table->dropForeign(['kelas_id']); // Hapus relasi ke tabel kelas
                $table->dropColumn('kelas_id');   // Hapus kolom kelas_id
            }

            // 2. Tambahkan kolom nama_kelas (teks bebas)
            if (!Schema::hasColumn('siswas', 'nama_kelas')) {
                $table->string('nama_kelas')->nullable()->after('nama_siswa');
            }
        });
    }

    /**
     * Batalkan perubahan jika migrasi di-rollback.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'nama_kelas')) {
                $table->dropColumn('nama_kelas');
            }

            if (!Schema::hasColumn('siswas', 'kelas_id')) {
                $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('set null')->after('user_id');
            }
        });
    }
};