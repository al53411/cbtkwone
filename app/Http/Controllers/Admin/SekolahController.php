<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SekolahController extends Controller
{
    /**
     * Tampilkan Profil Sekolah milik user yang sedang login
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $sekolah = null;

        // 1. Jika Superadmin
        if ($user && $user->role === 'superadmin') {
            // Mengakomodasi filter query jika superadmin memilih sekolah tertentu (?sekolah_id=1)
            if ($request->has('sekolah_id')) {
                $sekolah = Sekolah::find($request->sekolah_id);
            }

            // Fallback jika tidak ada query param
            if (!$sekolah) {
                $sekolah = $user->sekolah_id
                    ? Sekolah::find($user->sekolah_id)
                    : Sekolah::first();
            }
        } else {
            // 2. Jika Admin Sekolah / Kepsek: Murni ambil berdasarkan sekolah_id miliknya
            $sekolah = ($user && $user->sekolah_id) ? Sekolah::find($user->sekolah_id) : null;
        }

        // 3. Jika data sekolah tetap tidak ditemukan (Database kosong / User belum terikat)
        if (!$sekolah) {
            $sekolah = new Sekolah();

            $warningMsg = ($user && $user->role === 'superadmin')
                ? 'Belum ada data sekolah terdaftar. Silakan tambahkan profil sekolah pertama.'
                : 'Akun Anda belum terhubung dengan unit sekolah manapun. Silakan hubungi Superadmin.';

            return view('admin.sekolah.index', [
                'sekolah' => $sekolah,
                'warning' => $warningMsg
            ]);
        }

        return view('admin.sekolah.index', compact('sekolah'));
    }

    /**
     * Update / Simpan Data Sekolah, Proktor, & Pengawas Ujian
     */
    public function update(Request $request, $id = null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // A. JIKA USER ADALAH ADMIN SEKOLAH / KEPSEK (BUKAN SUPERADMIN)
        if ($user->role !== 'superadmin') {
            // Proteksi 1: Wajib memiliki sekolah_id
            if (!$user->sekolah_id) {
                return redirect()->back()->with('error', 'Akun Anda tidak terikat dengan sekolah manapun.');
            }

            // Proteksi 2: Paksa update HANYA pada sekolah miliknya (Abaikan ID dari URL)
            $sekolah = Sekolah::findOrFail($user->sekolah_id);

            $validated = $request->validate([
                'telepon'       => 'nullable|string|max:50',
                'email'         => 'nullable|email|max:255',
                'nama_proktor'  => 'nullable|string|max:255',
                'nip_proktor'   => 'nullable|string|max:50',
                'nama_pengawas' => 'nullable|string|max:255',
                'nip_pengawas'  => 'nullable|string|max:50',
            ], [
                'email.email'        => 'Format email sekolah tidak valid.',
                'nama_proktor.max'   => 'Nama proktor maksimal 255 karakter.',
                'nip_proktor.max'    => 'NIP/NUPTK proktor maksimal 50 karakter.',
                'nama_pengawas.max'  => 'Nama pengawas maksimal 255 karakter.',
                'nip_pengawas.max'   => 'NIP/NUPTK pengawas maksimal 50 karakter.',
            ]);

            $sekolah->update($validated);

            return redirect()->back()->with('success', 'Kontak sekolah, data proktor, dan pengawas berhasil diperbarui!');
        }

        // B. JIKA USER ADALAH SUPERADMIN
        $sekolah = ($id && $id != 0) ? Sekolah::find($id) : new Sekolah();

        $validated = $request->validate([
            'npsn'           => 'required|numeric|unique:sekolahs,npsn,' . ($sekolah->id ?? 'NULL'),
            'nama_sekolah'   => 'required|string|max:255',
            'jenjang'        => 'required|string',
            'status'         => 'required|string',
            'alamat'         => 'required|string',
            'desa_kelurahan' => 'nullable|string|max:255',
            'kecamatan'      => 'nullable|string|max:255',
            'kabupaten_kota' => 'nullable|string|max:255',
            'provinsi'       => 'nullable|string|max:255',
            'nama_kepsek'    => 'nullable|string|max:255',
            'nip_kepsek'     => 'nullable|string|max:255',
            'telepon'        => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'nama_proktor'   => 'nullable|string|max:255',
            'nip_proktor'    => 'nullable|string|max:50',
            'nama_pengawas'  => 'nullable|string|max:255',
            'nip_pengawas'   => 'nullable|string|max:50',
        ]);

        $sekolah->fill($validated)->save();

        // Ikat superadmin ke sekolah ini HANYA jika akun superadmin belum punya sekolah_id sama sekali
        if (!$user->sekolah_id) {
            $user->update(['sekolah_id' => $sekolah->id]);
        }

        return redirect()->back()->with('success', 'Seluruh data sekolah, proktor, dan pengawas berhasil diperbarui!');
    }
}
