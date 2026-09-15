<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modul_ajars', function (Blueprint $table) {
            // Hapus foreign key lama yang merujuk ke mata_pelajarans
            $table->dropForeign(['mapel_id']);

            // Buat foreign key baru yang merujuk ke tabel mapels
            $table->foreign('mapel_id')
                  ->references('id')
                  ->on('mapels')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('modul_ajars', function (Blueprint $table) {
            $table->dropForeign(['mapel_id']);
            $table->foreign('mapel_id')
                  ->references('id')
                  ->on('mata_pelajarans')
                  ->onDelete('cascade');
        });
    }
};
