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
        if (Schema::hasTable('sekolahs') && !Schema::hasColumn('sekolahs', 'logo')) {
            Schema::table('sekolahs', function (Blueprint $table) {
                $table->string('logo')->nullable()->after('nama_sekolah');
            });
        }

        if (Schema::hasTable('siswas') && !Schema::hasColumn('siswas', 'foto')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->string('foto')->nullable()->after('alamat');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sekolahs') && Schema::hasColumn('sekolahs', 'logo')) {
            Schema::table('sekolahs', function (Blueprint $table) {
                $table->dropColumn('logo');
            });
        }

        if (Schema::hasTable('siswas') && Schema::hasColumn('siswas', 'foto')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropColumn('foto');
            });
        }
    }
};
