<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kelas_mapel')) {
            Schema::create('kelas_mapel', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
                
                // Gunakan pengecekan agar tidak error jika tabel mapels belum terbaca
                if (Schema::hasTable('mapels')) {
                    $table->foreignId('mapel_id')->constrained('mapels')->onDelete('cascade');
                } else {
                    $table->unsignedBigInteger('mapel_id')->nullable();
                }
                
                // Opsional: Jika ingin menentukan guru pengampu spesifik per kelas
                if (Schema::hasTable('gurus')) {
                    $table->foreignId('guru_id')->nullable()->constrained('gurus')->onDelete('set null');
                } else {
                    $table->unsignedBigInteger('guru_id')->nullable();
                }
                
                $table->timestamps();

                // Mencegah duplikasi pasangan kelas dan mapel yang sama
                $table->unique(['kelas_id', 'mapel_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_mapel');
    }
};