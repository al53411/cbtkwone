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
        Schema::table('siswas', function (Blueprint $table) {
            $table->foreignId('kelas_id')
                  ->nullable()
                  ->after('jenis_kelamin')
                  ->constrained('kelas')
                  ->nullOnDelete(); // Opsional: set null jika data kelas dihapus
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // Hapus foreign key constraint dan kolom kelas_id
            $table->dropForeign(['kelas_id']);
            $table->dropColumn('kelas_id');
        });
    }
};