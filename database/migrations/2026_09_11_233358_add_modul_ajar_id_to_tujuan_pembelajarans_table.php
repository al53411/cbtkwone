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
        Schema::table('tujuan_pembelajarans', function (Blueprint $table) {
            $table->foreignId('modul_ajar_id')->nullable()->after('id')->constrained('modul_ajars')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tujuan_pembelajarans', function (Blueprint $table) {
            $table->dropForeign(['modul_ajar_id']);
            $table->dropColumn('modul_ajar_id');
        });
}
};
