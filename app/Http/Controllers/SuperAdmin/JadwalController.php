<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $sekarang = Carbon::now('Asia/Jakarta');

        // Sesuaikan nama relasi ('mapel', 'kelas', 'guru') dengan model Ujian Anda
        $query = Ujian::with(['mapel', 'kelas'])
            ->orderBy('waktu_mulai', 'asc');

        // Filter berdasarkan tanggal jika dipilih
        if ($request->filled('tanggal')) {
            $query->whereDate('waktu_mulai', $request->tanggal);
        }

        // Filter berdasarkan kelas jika dipilih
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter berdasarkan status (berlangsung, akan_datang, selesai)
        if ($request->status === 'berlangsung') {
            $query->where('waktu_mulai', '<=', $sekarang)
                ->where('waktu_selesai', '>=', $sekarang);
        } elseif ($request->status === 'akan_datang') {
            $query->where('waktu_mulai', '>', $sekarang);
        } elseif ($request->status === 'selesai') {
            $query->where('waktu_selesai', '<', $sekarang);
        }

        $semuaUjian = $query->get();

        // Kelompokkan jadwal berdasarkan Hari & Tanggal (contoh: "Senin, 16 November 2026")
        $jadwalPerTanggal = $semuaUjian->groupBy(function ($item) {
            return Carbon::parse($item->waktu_mulai)
                ->locale('id')
                ->translatedFormat('l, d F Y');
        });

        // Ringkasan statistik cepat untuk header jadwal
        $ringkasan = [
            'total'       => Ujian::count(),
            'hari_ini'    => Ujian::whereDate('waktu_mulai', $sekarang->toDateString())->count(),
            'berlangsung' => Ujian::where('waktu_mulai', '<=', $sekarang)->where('waktu_selesai', '>=', $sekarang)->count(),
            'akan_datang' => Ujian::where('waktu_mulai', '>', $sekarang)->count(),
        ];

        $daftarKelas = class_exists(Kelas::class) ? Kelas::orderBy('nama_kelas')->get() : collect();

        return view('superadmin.jadwal.index', compact(
            'jadwalPerTanggal',
            'ringkasan',
            'daftarKelas',
            'sekarang'
        ));
    }
}
