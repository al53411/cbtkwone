<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruang;
use App\Models\Sesi;
use App\Models\Ujian;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RuangSesiController extends Controller
{
    /**
     * Tampilkan Halaman Utama Ruang & Sesi Ujian
     */
    public function index(Request $request)
    {
        $sekolahId = Auth::user()->sekolah_id;

        // Fetch Data Ujian sesuai tabel ujitans
        $query = Ujian::with(['mapel', 'kelas']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $ujians = $query->latest()->paginate(10);

        // Fetch Data Master Tambahan
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        // Fetch Data Ruang & Sesi Existing
        $ruangList = Ruang::where('sekolah_id', $sekolahId)->latest()->get();
        $sesiList  = Sesi::where('sekolah_id', $sekolahId)->orderBy('waktu_mulai', 'asc')->get();

        $totalRuang     = $ruangList->count();
        $totalSesi      = $sesiList->count();
        $totalKapasitas = $ruangList->sum('kapasitas');

        return view('admin.sesi.index', compact(
            'ujians',
            'mapels',
            'kelass',
            'ruangList',
            'sesiList',
            'totalRuang',
            'totalSesi',
            'totalKapasitas'
        ));
    }

    /**
     * Simpan / Update Data Ruang
     */
    public function storeRuang(Request $request)
    {
        $sekolahId = Auth::user()->sekolah_id;

        $request->validate([
            'kode_ruang' => 'required|string|max:50',
            'nama_ruang' => 'required|string|max:100',
            'kapasitas'  => 'required|integer|min:1',
        ]);

        if ($request->filled('id')) {
            $ruang = Ruang::where('sekolah_id', $sekolahId)->findOrFail($request->id);
            $ruang->update([
                'kode_ruang' => $request->kode_ruang,
                'nama_ruang' => $request->nama_ruang,
                'kapasitas'  => $request->kapasitas,
            ]);
        } else {
            Ruang::create([
                'sekolah_id' => $sekolahId,
                'kode_ruang' => $request->kode_ruang,
                'nama_ruang' => $request->nama_ruang,
                'kapasitas'  => $request->kapasitas,
            ]);
        }

        return redirect()->back()->with('success', 'Data ruang berhasil disimpan!');
    }

    /**
     * Update Data Ujian / Sesi Ujian (Termasuk Token Spesifik Mapel)
     */
    public function updateSesi(Request $request, $id)
    {
        $request->validate([
            'nama_ujian'    => 'required|string|max:255',
            'mapel_id'      => 'required|exists:mapels,id',
            'kelas_id'      => 'required|exists:kelas,id',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'durasi'        => 'required|integer|min:1',
            'status'        => 'required|in:draft,aktif,selesai',
        ], [
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        $ujian = Ujian::findOrFail($id);

        $karakter = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        
        // Jika token kosong atau ingin digenerate ulang khusus untuk mapel ini
        $token = $request->filled('token') 
            ? strtoupper(trim($request->token)) 
            : ($ujian->token ?? substr(str_shuffle(str_repeat($karakter, 5)), 0, 6));

        $ujian->update([
            'nama_ujian'    => $request->nama_ujian,
            'mapel_id'      => $request->mapel_id,
            'kelas_id'      => $request->kelas_id,
            'waktu_mulai'   => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'durasi'        => $request->durasi,
            'status'        => $request->status,
            'token'         => $token,
            'acak_soal'     => $request->has('acak_soal') ? 1 : 0,
            'acak_jawaban'  => $request->has('acak_jawaban') ? 1 : 0,
        ]);

        return redirect()->route('admin.sesi.index')->with('success', 'Data ujian dan token mapel berhasil diperbarui!');
    }

    /**
     * Hapus Data Ruang
     */
    public function destroyRuang($id)
    {
        $ruang = Ruang::where('sekolah_id', Auth::user()->sekolah_id)->findOrFail($id);
        $ruang->delete();

        return redirect()->back()->with('success', 'Data ruang berhasil dihapus!');
    }

    /**
     * Simpan / Update Data Sesi (Waktu Sesi)
     */
    public function storeSesi(Request $request)
    {
        $sekolahId = Auth::user()->sekolah_id;

        $request->validate([
            'nama_sesi'     => 'required|string|max:50',
            'waktu_mulai'   => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
        ], [
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        if ($request->filled('id')) {
            $sesi = Sesi::where('sekolah_id', $sekolahId)->findOrFail($request->id);
            $sesi->update([
                'nama_sesi'     => $request->nama_sesi,
                'waktu_mulai'   => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
            ]);
        } else {
            Sesi::create([
                'sekolah_id'    => $sekolahId,
                'nama_sesi'     => $request->nama_sesi,
                'waktu_mulai'   => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
            ]);
        }

        return redirect()->back()->with('success', 'Data sesi berhasil disimpan!');
    }

    /**
     * Hapus Data Sesi
     */
    public function destroySesi($id)
    {
        $sesi = Sesi::where('sekolah_id', Auth::user()->sekolah_id)->findOrFail($id);
        $sesi->delete();

        return redirect()->back()->with('success', 'Data sesi berhasil dihapus!');
    }
}