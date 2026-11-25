<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KepsekController extends Controller
{
    /**
     * Tampilkan data Kepala Sekolah, Username, dan Status Account
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['kepsek', 'Kepsek', 'KEPSEK', 'kepala_sekolah'])
                     ->with('sekolah');

        // Fitur pencarian berdasarkan Nama, Email/Username, NIP, atau Sekolah
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%") // <-- Pencarian NIP ditambahkan
                  ->orWhereHas('sekolah', function($s) use ($search) {
                      $s->where('nama_sekolah', 'like', "%{$search}%");
                  });
            });
        }

        $kepseks = $query->latest()->paginate(10);
        $kepala_sekolahs = $kepseks; // Alias aman jika view memanggil $kepala_sekolahs

        // Mengirimkan kedua variabel agar Blade tidak error apapun pilihan pemanggilannya
        return view('superadmin.kepsek.index', compact('kepseks', 'kepala_sekolahs'));
    }

    /**
     * Form tambah Kepala Sekolah
     */
    public function create()
    {
        // Mengambil data sekolah dengan urutan nama agar rapi di dropdown
        $sekolahs = Sekolah::orderBy('nama_sekolah', 'asc')->get();
        return view('superadmin.kepsek.create', compact('sekolahs'));
    }

    /**
     * Simpan data Kepala Sekolah baru
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (NIP Ditambahkan)
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email',
            'nip'        => 'nullable|string|max:50', // <-- Validasi NIP
            'password'   => 'required|string|min:8',
            'sekolah_id' => 'nullable|exists:sekolahs,id',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.unique'      => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'sekolah_id.exists' => 'Sekolah yang dipilih tidak valid.',
        ]);

        // 2. Simpan Data ke DB (NIP Ditambahkan)
        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'nip'        => $request->nip ?? null, // <-- Kirim NIP ke DB
            'password'   => Hash::make($request->password),
            'role'       => 'kepsek',
            'sekolah_id' => $request->sekolah_id,
        ]);

        return redirect()->route('superadmin.kepsek.index')
                         ->with('success', 'Data Kepala Sekolah berhasil ditambahkan!');
    }

    /**
     * Reset password kepala sekolah
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', "Password untuk {$user->name} berhasil diperbarui!");
    }
}