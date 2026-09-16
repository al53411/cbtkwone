<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $sekolahId = Auth::user()->sekolah_id;
        $tahunAjaran = TahunAjaran::where('sekolah_id', $sekolahId)->latest()->get();

        return view('admin.tahun_ajaran.index', compact('tahunAjaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun'    => 'required|string',
            'semester' => 'required|in:Ganjil,Genap',
        ]);

        TahunAjaran::create([
            'sekolah_id' => Auth::user()->sekolah_id,
            'tahun'      => $request->tahun,
            'semester'   => $request->semester,
            'is_aktif'   => false,
        ]);

        return redirect()->back()->with('success', 'Tahun Ajaran berhasil ditambahkan!');
    }

    public function setAktif($id)
    {
        $sekolahId = Auth::user()->sekolah_id;

        // Reset semua status aktif untuk sekolah ini
        TahunAjaran::where('sekolah_id', $sekolahId)->update(['is_aktif' => false]);

        // Aktifkan tahun ajaran yang dipilih
        $ta = TahunAjaran::where('sekolah_id', $sekolahId)->findOrFail($id);
        $ta->update(['is_aktif' => true]);

        return redirect()->back()->with('success', 'Tahun Ajaran aktif berhasil diperbarui!');
    }
}