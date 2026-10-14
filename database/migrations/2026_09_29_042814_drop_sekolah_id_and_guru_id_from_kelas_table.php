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
        // 1. Lepas Foreign Key Constraint terlebih dahulu jika ada
        Schema::table('kelas', function (Blueprint $table) {
            try {
                $table->dropForeign('kelas_sekolah_id_foreign');
            } catch (\Exception $e) {
                // Abaikan jika FK tidak ada
            }

            try {
                $table->dropForeign('kelas_guru_id_foreign');
            } catch (\Exception $e) {
                // Abaikan jika FK tidak ada
            }
        });

        // 2. Hapus kolom sekolah_id, guru_id, dan wali_kelas
        Schema::table('kelas', function (Blueprint $table) {
            if (Schema::hasColumn('kelas', 'sekolah_id')) {
                $table->dropColumn('sekolah_id');
            }
            if (Schema::hasColumn('kelas', 'guru_id')) {
                $table->dropColumn('guru_id');
            }
            if (Schema::hasColumn('kelas', 'wali_kelas')) {
                $table->dropColumn('wali_kelas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('sekolah_id')->nullable();
            $table->unsignedBigInteger('guru_id')->nullable();
            $table->string('wali_kelas')->nullable();
        });
    }
};