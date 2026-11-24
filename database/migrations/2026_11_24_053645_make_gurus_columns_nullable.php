<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->change();
            $table->string('tempat_lahir')->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->enum('jenis_kelamin', ['L','P'])->nullable()->change();
            $table->string('nama_ibu_kandung')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->string('nik', 16)->nullable(false)->change();
            $table->string('tempat_lahir')->nullable(false)->change();
            $table->date('tanggal_lahir')->nullable(false)->change();
            $table->enum('jenis_kelamin', ['L','P'])->nullable(false)->change();
            $table->string('nama_ibu_kandung')->nullable(false)->change();
        });
    }
};
