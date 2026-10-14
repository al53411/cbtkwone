<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Guru;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Akun Superadmin Utama
            User::firstOrCreate(
                ['email' => 'superadmin@admin.com'],
                [
                    'name'     => 'Super Admin',
                    'password' => Hash::make('password123'),
                    'role'     => 'superadmin',
                ]
            );

            // 2. Akun Admin Sekolah
            User::firstOrCreate(
                ['email' => 'admin@sekolah.com'],
                [
                    'name'     => 'Admin Sekolah',
                    'password' => Hash::make('password123'),
                    'role'     => 'admin',
                ]
            );

            // 3. Akun Guru
            $nipGuru = '198501012010011001';

            $userGuru = User::firstOrCreate(
                ['email' => $nipGuru . '@sekolah.id'],
                [
                    'name'     => 'Guru Contoh, S.Pd.',
                    'password' => Hash::make($nipGuru),
                    'role'     => 'guru',
                ]
            );

            // 4. Data Detail Guru
            Guru::firstOrCreate(
                ['nip' => $nipGuru],
                [
                    'user_id'             => $userGuru->id,
                    'nik'                 => '3201234567890001',
                    'nama_lengkap'        => 'Guru Contoh, S.Pd.',
                    'tempat_lahir'        => 'Jakarta',
                    'tanggal_lahir'       => '1985-01-01',
                    'jenis_kelamin'       => 'L',
                    'nama_ibu_kandung'    => 'Ibu Contoh',
                    'status_kepegawaian'  => 'PNS',
                    'pendidikan_terakhir' => 'S1 Pendidikan',
                ]
            );
        });
    }
}