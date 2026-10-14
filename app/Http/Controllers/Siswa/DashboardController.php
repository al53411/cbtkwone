<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $siswa = Auth::guard('siswa')->user();
        
        // Ambil daftar ujian yang sedang aktif untuk kelas siswa atau umum (null)
        $ujianAktif = Ujian::with(['mapel', 'kelas'])
            ->where('status', 'aktif')
            ->where(function($q) use ($siswa) {
                if ($siswa && isset($siswa->kelas_id)) {
                    $q->where('kelas_id', $siswa->kelas_id)
                      ->orWhereNull('kelas_id');
                }
            })
            ->get();

        return view('siswa.dashboard', compact('siswa', 'ujianAktif'));
    }

    public function konfirmasiUjian(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:10',
        ], [
            'token.required' => 'Token ujian wajib diisi.',
        ]);

        $siswa = Auth::guard('siswa')->user();

        if (!$siswa) {
            return redirect()->route('siswa.login')
                ->with('error', 'Sesi Anda telah berakhir, silakan login kembali.');
        }

        $inputToken = strtoupper(trim($request->token));

        // Cari ujian yang statusnya 'aktif' dan tokennya cocok
        $ujian = Ujian::where('status', 'aktif')
            ->where('token', $inputToken)
            ->first();

        if (!$ujian) {
            return back()
                ->withErrors(['token' => 'Token Ujian tidak valid atau ujian belum aktif!'])
                ->with('error', 'Token Ujian tidak valid atau ujian belum aktif!')
                ->withInput();
        }

        // Simpan ID ujian ke dalam session siswa
        session(['current_ujian_id' => $ujian->id]);

        return redirect()->route('siswa.ujian.mulai')
            ->with('success', 'Token valid! Selamat mengerjakan ujian ' . ($ujian->mapel->nama_mapel ?? ''));
    }
}