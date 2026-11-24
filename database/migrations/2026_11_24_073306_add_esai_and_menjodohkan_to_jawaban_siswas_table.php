<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jawaban_siswas', function (Blueprint $table) {
            // Untuk menampung jawaban esai/uraian panjang maupun JSON pasangan menjodohkan
            if (!Schema::hasColumn('jawaban_siswas', 'jawaban_teks')) {
                $table->longText('jawaban_teks')->nullable()->after('jawaban');
            }

            // Opsional: khusus untuk format JSON menjodohkan / PG Kompleks (contoh: {"1":"B","2":"A"})
            if (!Schema::hasColumn('jawaban_siswas', 'jawaban_json')) {
                $table->json('jawaban_json')->nullable()->after('jawaban_teks');
            }

            // Skor hasil koreksi (dari korektor untuk esai, atau hitung otomatis untuk menjodohkan)
            if (!Schema::hasColumn('jawaban_siswas', 'skor_esai')) {
                $table->decimal('skor_esai', 6, 2)->nullable()->after('is_benar');
            }

            // Catatan/komentar korektor (opsional)
            if (!Schema::hasColumn('jawaban_siswas', 'catatan_korektor')) {
                $table->text('catatan_korektor')->nullable()->after('skor_esai');
            }
        });

        // Tambahkan rincian nilai PG & Esai pada tabel rekap nilai_ujians
        Schema::table('nilai_ujians', function (Blueprint $table) {
            if (!Schema::hasColumn('nilai_ujians', 'nilai_pg')) {
                $table->decimal('nilai_pg', 5, 2)->default(0.00)->after('tidak_dijawab');
            }
            if (!Schema::hasColumn('nilai_ujians', 'nilai_esai')) {
                $table->decimal('nilai_esai', 5, 2)->default(0.00)->after('nilai_pg');
            }
            if (!Schema::hasColumn('nilai_ujians', 'status_koreksi')) {
                $table->string('status_koreksi', 30)->default('belum_dikoreksi')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jawaban_siswas', function (Blueprint $table) {
            $cols = array_filter(['jawaban_teks', 'jawaban_json', 'skor_esai', 'catatan_korektor'], fn($c) => Schema::hasColumn('jawaban_siswas', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('nilai_ujians', function (Blueprint $table) {
            $cols = array_filter(['nilai_pg', 'nilai_esai', 'status_koreksi'], fn($c) => Schema::hasColumn('nilai_ujians', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
