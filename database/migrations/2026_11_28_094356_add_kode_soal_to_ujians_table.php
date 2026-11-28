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
        Schema::table('ujians', function (Blueprint $table) {
            if (!Schema::hasColumn('ujians', 'kode_soal')) {
                $table->string('kode_soal', 100)->nullable()->after('mapel_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            if (Schema::hasColumn('ujians', 'kode_soal')) {
                $table->dropColumn('kode_soal');
            }
        });
    }
};
