<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mata_pelajarans')) {
            Schema::create('mata_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_mapel')->unique();
                $table->string('nama_mapel');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('capaian_pembelajarans')) {
            Schema::create('capaian_pembelajarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mapel_id')->constrained('mata_pelajarans')->onDelete('cascade');
                $table->enum('fase', ['A', 'B', 'C', 'D', 'E', 'F']);
                $table->string('elemen');
                $table->text('deskripsi_cp');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('modul_ajars')) {
            Schema::create('modul_ajars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('mapel_id')->constrained('mata_pelajarans')->onDelete('cascade');
                $table->foreignId('cp_id')->nullable()->constrained('capaian_pembelajarans')->nullOnDelete();
                
                $table->string('judul');
                $table->enum('fase', ['A', 'B', 'C', 'D', 'E', 'F']);
                $table->string('kelas');
                $table->string('alokasi_waktu');
                $table->string('tahun_ajaran')->default('2026/2027');
                $table->enum('semester', ['1', '2']);
                
                $table->text('kompetensi_awal')->nullable();
                $table->json('profil_pelajar_pancasila')->nullable();
                $table->text('sarana_prasarana')->nullable();
                $table->string('target_peserta_didik')->default('Reguler / Tipikal');
                $table->string('model_pembelajaran')->nullable();
                
                $table->text('pemahaman_bermakna')->nullable();
                $table->text('pertanyaan_pemantik')->nullable();
                $table->enum('status', ['draft', 'published'])->default('draft');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tujuan_pembelajarans')) {
            Schema::create('tujuan_pembelajarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('modul_ajar_id')->constrained('modul_ajars')->onDelete('cascade');
                $table->text('deskripsi_tp');
                $table->integer('urutan')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('langkah_pembelajarans')) {
            Schema::create('langkah_pembelajarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('modul_ajar_id')->constrained('modul_ajars')->onDelete('cascade');
                $table->integer('pertemuan_ke');
                $table->text('kegiatan_awal');
                $table->text('kegiatan_inti');
                $table->text('kegiatan_penutup');
                $table->integer('alokasi_menit')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('asesmens')) {
            Schema::create('asesmens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('modul_ajar_id')->constrained('modul_ajars')->onDelete('cascade');
                $table->enum('jenis_asesmen', ['diagnostik', 'formatif', 'sumatif']);
                $table->string('teknik_penilaian');
                $table->text('instrumen_penilaian')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('modul_lampirans')) {
            Schema::create('modul_lampirans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('modul_ajar_id')->constrained('modul_ajars')->onDelete('cascade');
                $table->enum('tipe', ['lkpd', 'bahan_bacaan', 'glosarium', 'daftar_pustaka', 'file_tambahan']);
                $table->string('judul_lampiran');
                $table->text('konten_teks')->nullable();
                $table->string('file_path')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modul_lampirans');
        Schema::dropIfExists('asesmens');
        Schema::dropIfExists('langkah_pembelajarans');
        Schema::dropIfExists('tujuan_pembelajarans');
        Schema::dropIfExists('modul_ajars');
        Schema::dropIfExists('capaian_pembelajarans');
        Schema::dropIfExists('mata_pelajarans');
    }
};