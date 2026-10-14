<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianController extends Controller
{
    public function mulai()
    {
        $siswa = Auth::guard('siswa')->user();
        $ujianId = session('current_ujian_id');

        if (!$ujianId) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Silakan masukkan token ujian terlebih dahulu.');
        }

        $ujian = Ujian::with(['mapel', 'kelas'])->findOrFail($ujianId);

        // Ambil daftar soal berdasarkan mata pelajaran ujian
        $soals = Soal::where('mapel_id', $ujian->mapel_id)->get();

        return view('siswa.ujian.mulai', compact('siswa', 'ujian', 'soals'));
    }
}