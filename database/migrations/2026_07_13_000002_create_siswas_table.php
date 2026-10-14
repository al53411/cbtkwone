<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('sekolah_id')->nullable()->constrained('sekolahs')->onDelete('cascade');

            $table->string('nisn')->nullable()->unique();
            $table->string('nama_siswa');
            
            // Kolom nama_kelas menggantikan kelas_id (bisa diisi teks bebas dari Excel/form)
            $table->string('nama_kelas')->nullable();

            // Kolom pendukung
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->text('alamat')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};