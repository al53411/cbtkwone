<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;

class SoalController extends Controller
{
    public function paketIndex(Request $request)
    {
        $mapels = Mapel::all();
        $kelass = Kelas::all();

        $query = Soal::with(['mapel', 'kelas']);

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('soal', 'like', "%{$search}%")
                  ->orWhere('kode_soal', 'like', "%{$search}%");
            });
        }

        $pakets = $query->latest()->paginate(10)->withQueryString();

        return view('admin.soal.paket_index', compact('pakets', 'mapels', 'kelass'));
    }
}