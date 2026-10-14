<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel; 
use App\Models\Ujian;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    /**
     * Menampilkan Halaman Manajemen Token & Ujian
     */
    public function index(Request $request)
    {
        // 1. Ambil data Kelas dan Mapel untuk Dropdown Filter
        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $listMapel = Mapel::orderBy('nama_mapel', 'asc')->get();

        // 2. Query daftar Ujian
        $query = Ujian::with(['kelas', 'mapel']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->latest()->paginate(10)->appends($request->query());

        // 3. Ambil token dari ujian aktif terbaru (untuk tampilan Banner Ringkasan)
        $ujianAktif = Ujian::where('status', 'aktif')
            ->whereNotNull('token')
            ->latest('updated_at')
            ->first();

        $tokenAktif = (object) [
            'token' => $ujianAktif->token ?? '------',
            'nama_ujian' => $ujianAktif->nama_ujian ?? 'Tidak ada ujian aktif'
        ];

        return view('admin.token.index', compact('tokenAktif', 'listKelas', 'listMapel', 'ujians'));
    }

    /**
     * Rilis / Generate Token Baru (Spesifik per Ujian/Mapel)
     */
    public function generate(Request $request)
    {
        $karakter = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        // 1. Jika ada parameter 'ujian_id', update token untuk 1 ujian/mapel spesifik tersebut
        if ($request->filled('ujian_id')) {
            $ujian = Ujian::with('mapel')->findOrFail($request->ujian_id);
            
            $newToken = substr(str_shuffle(str_repeat($karakter, 5)), 0, 6);
            $ujian->update(['token' => $newToken]);

            return back()->with('success', "Token ($newToken) berhasil dirilis untuk mapel: " . ($ujian->mapel->nama_mapel ?? $ujian->nama_ujian));
        }

        // 2. Jika tidak ada 'ujian_id', generate token unik masing-masing untuk SEMUA ujian berstatus 'aktif'
        $ujiansAktif = Ujian::with('mapel')->where('status', 'aktif')->get();

        if ($ujiansAktif->isEmpty()) {
            return back()->with('error', 'Tidak ada ujian berstatus AKTIF yang dapat di-update tokennya.');
        }

        foreach ($ujiansAktif as $ujian) {
            $uniqueToken = substr(str_shuffle(str_repeat($karakter, 5)), 0, 6);
            $ujian->update(['token' => $uniqueToken]);
        }

        $count = $ujiansAktif->count();
        return back()->with('success', "Token unik berhasil dirilis untuk $count ujian/mapel yang sedang aktif!");
    }
}