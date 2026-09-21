<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $soals = Soal::latest()->paginate(10);
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        return view('superadmin.soal.index', compact('soals', 'mapels', 'kelass'));
    }

    public function create()
    {
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        return view('superadmin.soal.create', compact('mapels', 'kelass'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mapel_id'      => 'required',
            'kelas_id'      => 'required',
            'soal'          => 'required',
            'kunci_jawaban' => 'required',
        ]);

        Soal::create([
            'mapel_id'      => $request->mapel_id,
            'kelas_id'      => $request->kelas_id,
            'soal'          => $request->soal,
            'option_a'      => $request->option_a,
            'option_b'      => $request->option_b,
            'option_c'      => $request->option_c,
            'option_d'      => $request->option_d,
            'option_e'      => $request->option_e,
            'kunci_jawaban' => $request->kunci_jawaban,
        ]);

        return redirect()->route('superadmin.soal.index')->with('success', 'Soal berhasil ditambahkan!');
    }
}