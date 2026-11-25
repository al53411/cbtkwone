<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Tampilkan semua daftar pengguna (Kepala Sekolah / Proktor)
     */
    public function index(Request $request)
    {
        $query = User::with('sekolah');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);

        return view('superadmin.kepsek.index', compact('users'));
    }

    /**
     * Tampilkan form pembuatan akun baru
     */
    public function create()
    {
        $sekolahs = Sekolah::select('id', 'nama_sekolah', 'npsn', 'nama_kepsek', 'nip_kepsek', 'email')
            ->orderBy('nama_sekolah', 'asc')
            ->get();

        return view('superadmin.kepsek.create', compact('sekolahs'));
    }

    /**
     * Simpan akun baru ke database
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sekolah_id' => 'required|exists:sekolahs,id',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'nip'        => 'nullable|string|max:50',
            'password'   => 'required|string|min:8|confirmed',
        ], [
            'sekolah_id.required' => 'Pilih unit sekolah terlebih dahulu.',
            'sekolah_id.exists'   => 'Sekolah yang dipilih tidak valid.',
            'name.required'       => 'Nama lengkap wajib diisi.',
            'email.required'      => 'Alamat email wajib diisi.',
            'email.unique'        => 'Email ini sudah terdaftar di sistem.',
            'password.required'   => 'Password wajib diisi.',
            'password.min'        => 'Password minimal berisi 8 karakter.',
            'password.confirmed'  => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'sekolah_id'        => $validatedData['sekolah_id'],
            'name'              => $validatedData['name'],
            'email'             => $validatedData['email'],
            'nip'               => $validatedData['nip'] ?? null,
            'password'          => Hash::make($validatedData['password']),
            'role'              => 'kepsek', // Sesuaikan jika menggunakan 'admin' atau 'kepsek'
            'wajib_ganti_sandi' => true, // Memunculkan popup ganti sandi saat login pertama
        ]);

        return redirect()->route('superadmin.kepsek.index')
            ->with('success', 'Akun berhasil dibuat! Pengguna akan diminta mengganti sandi saat login pertama.');
    }

    /**
     * Tampilkan detail pengguna
     */
    public function show(string $id)
    {
        $user = User::with('sekolah')->findOrFail($id);
        return view('superadmin.kepsek.show', compact('user'));
    }

    /**
     * Tampilkan form edit pengguna
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $sekolahs = Sekolah::orderBy('nama_sekolah', 'asc')->get();

        return view('superadmin.kepsek.edit', compact('user', 'sekolahs'));
    }

    /**
     * Update data pengguna di database
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'sekolah_id' => 'required|exists:sekolahs,id',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email,' . $id,
            'nip'        => 'nullable|string|max:50',
            'password'   => 'nullable|string|min:8|confirmed',
        ], [
            'email.unique'       => 'Email ini sudah digunakan oleh akun lain.',
            'password.min'       => 'Password minimal berisi 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->sekolah_id = $validatedData['sekolah_id'];
        $user->name       = $validatedData['name'];
        $user->email      = $validatedData['email'];
        $user->nip        = $validatedData['nip'] ?? null;

        // Jika Superadmin mereset/mengisi password baru, wajibkan ganti sandi lagi saat login
        if ($request->filled('password')) {
            $user->password          = Hash::make($validatedData['password']);
            $user->wajib_ganti_sandi = true;
        }

        $user->save();

        return redirect()->route('superadmin.kepsek.index')
            ->with('success', 'Data akun berhasil diperbarui!');
    }

    /**
     * Hapus pengguna dari database
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('superadmin.kepsek.index')
            ->with('success', 'Akun berhasil dihapus!');
    }
}