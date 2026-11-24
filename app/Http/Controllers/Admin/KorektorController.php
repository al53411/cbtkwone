<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    public function create(Request $request)
    {
        $kelas = Kelas::when(
            Auth::user()->sekolah_id && Schema::hasColumn('kelas', 'sekolah_id'),
            fn($q) => $q->where('sekolah_id', Auth::user()->sekolah_id)
        )
            ->orderBy('nama_kelas')
            ->get();

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
            $userData = [
                'name'       => $validated['nama_lengkap'],
                'nip'        => $validated['nip'],
                'password'   => Hash::make($validated['nip']),
                'role'       => 'korektor',
                'sekolah_id' => $sekolahId,
            ];

            if (Schema::hasColumn('users', 'email')) {
                $userData['email'] = $validated['nip'] . '@korektor.local';
            }
            if (Schema::hasColumn('users', 'wajib_ganti_sandi')) {
                $userData['wajib_ganti_sandi'] = true;
            }

            $user = User::create($userData);

            $guru = Guru::create([
                'user_id'        => $user->id,
                'sekolah_id'     => $sekolahId,
                'nama_lengkap'   => $validated['nama_lengkap'],
                'nip'            => $validated['nip'],
                'nuptk'          => $validated['nuptk'] ?? null,
                'mata_pelajaran' => $validated['mata_pelajaran'],
                'jenis_guru'     => 'korektor',
            ]);

            $guru->kelas()->sync($validated['kelas_id']);
        });

        $pesan = "Korektor berhasil ditambahkan. Login menggunakan NIP: {$validated['nip']} dan Password: {$validated['nip']}";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $pesan,
            ]);
        }

        return redirect()->route('admin.korektor.index')->with('success', $pesan);
    }

    public function show(Guru $korektor)
    {
        return redirect()->route('admin.korektor.edit', $korektor->id);
    }

    public function edit(Guru $korektor)
    {
        $kelas = Kelas::when(
            Auth::user()->sekolah_id && Schema::hasColumn('kelas', 'sekolah_id'),
            fn($q) => $q->where('sekolah_id', Auth::user()->sekolah_id)
        )
            ->orderBy('nama_kelas')
            ->get();

        $selected = $korektor->kelas->pluck('id')->toArray();

        return view('admin.korektor.edit', compact('korektor', 'kelas', 'selected'));
    }

    public function update(Request $request, Guru $korektor)
    {
        $validated = $request->validate([
            'nama_lengkap'   => 'required|string|max:255',
            'nip'            => [
                'required',
                'string',
                'max:18',
                Rule::unique('gurus', 'nip')->ignore($korektor->id),
                Rule::unique('users', 'nip')->ignore($korektor->user_id),
            ],
            'nuptk'          => [
                'nullable',
                'string',
                'max:16',
                Rule::unique('gurus', 'nuptk')->ignore($korektor->id),
            ],
            'mata_pelajaran' => 'required|string|max:255',
            'kelas_id'       => 'required|array',
            'kelas_id.*'     => 'exists:kelas,id',
            'reset_password' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $validated, $korektor) {
            $korektor->update([
                'nama_lengkap'   => $validated['nama_lengkap'],
                'nip'            => $validated['nip'],
                'nuptk'          => $validated['nuptk'] ?? null,
                'mata_pelajaran' => $validated['mata_pelajaran'],
            ]);

            if ($korektor->user) {
                $updateUser = [
                    'name' => $validated['nama_lengkap'],
                    'nip'  => $validated['nip'],
                ];

                if (Schema::hasColumn('users', 'email') && str_ends_with((string) $korektor->user->email, '@korektor.local')) {
                    $updateUser['email'] = $validated['nip'] . '@korektor.local';
                }

                if ($request->boolean('reset_password')) {
                    $updateUser['password'] = Hash::make($validated['nip']);
                    if (Schema::hasColumn('users', 'wajib_ganti_sandi')) {
                        $updateUser['wajib_ganti_sandi'] = true;
                    }
                }

                $korektor->user->update($updateUser);
            } else {
                $userData = [
                    'name'       => $validated['nama_lengkap'],
                    'nip'        => $validated['nip'],
                    'password'   => Hash::make($validated['nip']),
                    'role'       => 'korektor',
                    'sekolah_id' => $korektor->sekolah_id ?? Auth::user()->sekolah_id,
                ];
                if (Schema::hasColumn('users', 'email')) {
                    $userData['email'] = $validated['nip'] . '@korektor.local';
                }
                if (Schema::hasColumn('users', 'wajib_ganti_sandi')) {
                    $userData['wajib_ganti_sandi'] = true;
                }

                $user = User::create($userData);
                $korektor->update(['user_id' => $user->id]);
            }

            $korektor->kelas()->sync($validated['kelas_id']);
        });

        $pesan = 'Data korektor dan akun login berhasil diperbarui.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $pesan,
            ]);
        }

        return redirect()->route('admin.korektor.index')->with('success', $pesan);
    }

    public function destroy(Request $request, Guru $korektor)
    {
        DB::transaction(function () use ($korektor) {
            if ($korektor->user) {
                $korektor->user->delete();
            }
            $korektor->kelas()->detach();
            $korektor->delete();
        });

        $pesan = 'Korektor dan akun login berhasil dihapus.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $pesan,
            ]);
        }

        return redirect()->route('admin.korektor.index')->with('success', $pesan);
    }
}
