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
            if (!Schema::hasColumn('soals', 'soal')) {
                $table->text('soal')->nullable();
            }
            if (!Schema::hasColumn('soals', 'option_a')) {
                $table->text('option_a')->nullable();
            }
            if (!Schema::hasColumn('soals', 'option_b')) {
                $table->text('option_b')->nullable();
            }
            if (!Schema::hasColumn('soals', 'option_c')) {
                $table->text('option_c')->nullable();
            }
            if (!Schema::hasColumn('soals', 'option_d')) {
                $table->text('option_d')->nullable();
            }
            if (!Schema::hasColumn('soals', 'option_e')) {
                $table->text('option_e')->nullable();
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
