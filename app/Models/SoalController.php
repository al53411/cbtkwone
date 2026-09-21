<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Mapel; // 1. Import model Mapel
use App\Models\Kelas; // Import model Kelas (jika ada)
use Illuminate\Http\Request;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        // Query data soal
        $soals = Soal::latest()->paginate(10);

        // 2. Ambil data mapel & kelas dari database
        $mapels = Mapel::all(); 
        $kelass = Kelas::all(); 

        // 3. Pastikan 'mapels' dan 'kelass' ada di dalam compact()
        return view('superadmin.soal.index', compact('soals', 'mapels', 'kelass'));
    }
}