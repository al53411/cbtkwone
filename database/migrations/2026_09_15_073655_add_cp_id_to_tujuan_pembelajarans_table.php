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
            $table->foreignId('cp_id')->nullable()->after('id')->constrained('capaian_pembelajarans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tujuan_pembelajarans', function (Blueprint $table) {
            $table->dropForeign(['cp_id']);
            $table->dropColumn('cp_id');
        });
    }
};
