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
        // Matikan pengecekan foreign key secara aman lintas database
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mapels');
        Schema::enableForeignKeyConstraints();

        Schema::create('mapels', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel');
            $table->string('nama_mapel');
            $table->string('kelompok')->default('Wajib');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mapels');
        Schema::enableForeignKeyConstraints();
    }
};