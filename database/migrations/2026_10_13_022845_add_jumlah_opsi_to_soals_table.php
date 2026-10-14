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
        Schema::table('soals', function (Blueprint $table) {
            if (!Schema::hasColumn('soals', 'jumlah_opsi')) {
                $table->integer('jumlah_opsi')->default(5)->after('tipe'); // Default 5 opsi (A-E)
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            //
        });
    }
};
