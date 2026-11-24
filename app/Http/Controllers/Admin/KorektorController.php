<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\User;

class KorektorController extends Controller
{
    public function index()
    {
        $korektors = Guru::where('jenis_guru', 'korektor')
            ->when(Auth::user()->sekolah_id, function ($q, $sekolahId) {
                $q->where('sekolah_id', $sekolahId);
            })
            ->with(['user', 'kelas'])
            ->latest()
            ->get();

        return view('admin.korektor.index', compact('korektors'));
    }

    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('admin.korektor.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap'   => 'required|string|max:255',
            'nip'            => 'required|string|max:18|unique:gurus,nip|unique:users,nip',
            'nuptk'          => 'nullable|string|max:16|unique:gurus,nuptk',
            'mata_pelajaran' => 'required|string|max:255',
            'kelas_id'       => 'required|array',
            'kelas_id.*'     => 'exists:kelas,id',
        ]);

        $sekolahId = Auth::user()->sekolah_id;

        DB::transaction(function () use ($validated, $sekolahId) {
            // 1. Buat akun User otomatis (username/nip: nip, password: nip)
            $user = User::create([
                'name'              => $validated['nama_lengkap'],
                'nip'               => $validated['nip'],
                'password'          => Hash::make($validated['nip']),
                'role'              => 'korektor', // Ubah ke 'guru' jika middleware route memakai role:guru
                'sekolah_id'        => $sekolahId,
                'wajib_ganti_sandi' => true,
            ]);

            // 2. Buat data Guru (jenis korektor) dan kaitkan ke User
            $guru = Guru::create([
                'user_id'        => $user->id,
                'sekolah_id'     => $sekolahId,
                'nama_lengkap'   => $validated['nama_lengkap'],
                'nip'            => $validated['nip'],
                'nuptk'          => $validated['nuptk'] ?? null,
                'mata_pelajaran' => $validated['mata_pelajaran'],
                'jenis_guru'     => 'korektor',
            ]);

            // 3. Kaitkan kelas yang diampu korektor
            $guru->kelas()->sync($validated['kelas_id']);
        });

        return redirect()->route('admin.korektor.index')
            ->with('success', "Korektor berhasil ditambahkan. Login menggunakan NIP: {$validated['nip']} dan Password: {$validated['nip']}");
    }

    public function edit(Guru $korektor)
    {
        $kelas    = Kelas::orderBy('nama_kelas')->get();
        $selected = $korektor->kelas->pluck('id')->toArray();
        return view('admin.korektor.edit', compact('korektor', 'kelas', 'selected'));
    }

    public function update(Request $request, Guru $korektor)
    {
        $validated = $request->validate([
            'nama_lengkap'   => 'required|string|max:255',
            'nip'            => 'required|string|max:18|unique:gurus,nip,' . $korektor->id . '|unique:users,nip,' . ($korektor->user_id ?? 'NULL'),
            'nuptk'          => 'nullable|string|max:16|unique:gurus,nuptk,' . $korektor->id,
            'mata_pelajaran' => 'required|string|max:255',
            'kelas_id'       => 'required|array',
            'kelas_id.*'     => 'exists:kelas,id',
            'reset_password' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $validated, $korektor) {
            // Update data Guru
            $korektor->update([
                'nama_lengkap'   => $validated['nama_lengkap'],
                'nip'            => $validated['nip'],
                'nuptk'          => $validated['nuptk'] ?? null,
                'mata_pelajaran' => $validated['mata_pelajaran'],
            ]);

            // Update atau buat akun User jika belum ada
            if ($korektor->user) {
                $updateUser = [
                    'name' => $validated['nama_lengkap'],
                    'nip'  => $validated['nip'],
                ];

                if ($request->boolean('reset_password')) {
                    $updateUser['password'] = Hash::make($validated['nip']);
                    $updateUser['wajib_ganti_sandi'] = true;
                }

                $korektor->user->update($updateUser);
            } else {
                $user = User::create([
                    'name'              => $validated['nama_lengkap'],
                    'nip'               => $validated['nip'],
                    'password'          => Hash::make($validated['nip']),
                    'role'              => 'korektor',
                    'sekolah_id'        => $korektor->sekolah_id ?? Auth::user()->sekolah_id,
                    'wajib_ganti_sandi' => true,
                ]);
                $korektor->update(['user_id' => $user->id]);
            }

            $korektor->kelas()->sync($validated['kelas_id']);
        });

        return redirect()->route('admin.korektor.index')
            ->with('success', 'Data korektor dan akun login berhasil diperbarui.');
    }

    public function destroy(Guru $korektor)
    {
        DB::transaction(function () use ($korektor) {
            if ($korektor->user) {
                $korektor->user->delete();
            }
            $korektor->kelas()->detach();
            $korektor->delete();
        });

        return redirect()->route('admin.korektor.index')
            ->with('success', 'Korektor dan akun login berhasil dihapus.');
    }
}
