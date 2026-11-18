<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            if (!Schema::hasColumn('sekolahs', 'nama_proktor')) {
                $table->string('nama_proktor')->nullable()->after('nip_kepsek');
            }
            if (!Schema::hasColumn('sekolahs', 'nip_proktor')) {
                $table->string('nip_proktor', 30)->nullable()->after('nama_proktor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->dropColumn(['nama_proktor', 'nip_proktor']);
        });
    }
};
