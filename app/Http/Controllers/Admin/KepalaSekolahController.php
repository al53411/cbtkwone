<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Sekolah;
use App\Models\JurnalGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class KepalaSekolahController extends Controller
{
    /**
     * Tampilkan daftar Kepala Sekolah / Proktor (Superadmin).
     */
    public function index()
    {
        $kepalaSekolahs = User::where('role', 'kepsek')
            ->with('sekolah')
            ->latest()
            ->paginate(10);

        $users = $kepalaSekolahs;

        return view('superadmin.kepsek.index', compact('kepalaSekolahs', 'users'));
    }

    /**
     * Menampilkan form tambah akun Kepala Sekolah (Superadmin).
     */
    public function create()
    {
        $sekolahs = Sekolah::orderBy('nama_sekolah', 'asc')->get();

        return view('superadmin.kepsek.create', compact('sekolahs'));
    }

    /**
     * Menyimpan data akun Kepala Sekolah baru ke database (Superadmin)
     * sekaligus menyinkronkan Nama & NIP Kepala Sekolah ke tabel sekolahs.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
            'sekolah_id' => ['required', 'exists:sekolahs,id'],
            'nip'        => ['nullable', 'string', 'max:30', 'unique:users,nip'],
        ], [
            'name.required'       => 'Nama lengkap wajib diisi.',
            'email.required'      => 'Email wajib diisi.',
            'email.unique'        => 'Email tersebut sudah terdaftar di sistem.',
            'password.required'   => 'Password wajib diisi.',
            'password.confirmed'  => 'Konfirmasi password tidak cocok.',
            'sekolah_id.required' => 'Silakan pilih unit sekolah terlebih dahulu.',
            'sekolah_id.exists'   => 'Unit sekolah yang dipilih tidak valid.',
            'nip.unique'          => 'NIP/NIK tersebut sudah terdaftar.',
        ]);

        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role'              => 'kepsek',
            'sekolah_id'        => $request->sekolah_id,
            'nip'               => $request->nip,
            'wajib_ganti_sandi' => true,
        ]);

        // Sinkronkan otomatis ke tabel sekolahs agar kartu ujian & cetak hasil langsung dinamis
        Sekolah::where('id', $request->sekolah_id)->update([
            'nama_kepsek' => $request->name,
            'nip_kepsek'  => $request->nip,
        ]);

        return redirect()
            ->route('superadmin.kepsek.index')
            ->with('success', 'Akun Kepala Sekolah "' . $request->name . '" berhasil dibuat dan disinkronkan ke profil sekolah!');
    }

    /**
     * Reset Password akun Kepala Sekolah oleh Superadmin.
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::where('role', 'kepsek')->findOrFail($id);
        $passwordDefault = $request->input('password', '12345678');

        $user->update([
            'password'          => Hash::make($passwordDefault),
            'wajib_ganti_sandi' => true,
        ]);

        return back()->with('success', 'Password untuk "' . $user->name . '" berhasil direset ke: ' . $passwordDefault);
    }

    /**
     * Hapus akun Kepala Sekolah (Superadmin).
     */
    public function destroy($id)
    {
        $user = User::where('role', 'kepsek')->findOrFail($id);
        $nama = $user->name;
        $user->delete();

        return back()->with('success', 'Akun Kepala Sekolah "' . $nama . '" berhasil dihapus.');
    }

    /**
     * Menampilkan daftar validasi jurnal guru untuk Kepala Sekolah.
     */
    public function indexValidasiJurnal(Request $request)
    {
        $status = $request->input('status', 'Pending');
        $user   = Auth::user();

        $jurnals = JurnalGuru::with(['guru', 'kelas'])
            ->when($user && $user->sekolah_id, function ($query) use ($user) {
                return $query->whereHas('guru', function ($q) use ($user) {
                    $q->where('sekolah_id', $user->sekolah_id);
                });
            })
            ->when($status, function ($query, $status) {
                return $query->where('status_validasi', $status);
            })
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return view('admin.kepala-sekolah.validasi_jurnal', compact('jurnals', 'status'));
    }

    /**
     * Mengupdate status validasi jurnal.
     */
    public function updateStatusJurnal(Request $request, $id)
    {
        $request->validate([
            'status_validasi' => 'required|in:Disetujui,Ditolak',
            'catatan_kepsek'  => $request->status_validasi == 'Ditolak' ? 'required|string|min:3' : 'nullable|string',
        ], [
            'status_validasi.required' => 'Status validasi wajib dipilih.',
            'catatan_kepsek.required'  => 'Catatan wajib diisi jika jurnal ditolak.',
        ]);

        $jurnal = JurnalGuru::findOrFail($id);

        $jurnal->update([
            'status_validasi'  => $request->status_validasi,
            'catatan_kepsek'   => $request->status_validasi == 'Ditolak' ? $request->catatan_kepsek : null,
            'tanggal_validasi' => now(),
        ]);

        return redirect()->back()->with('success', 'Status validasi jurnal berhasil diperbarui.');
    }

    /**
     * Alias method update biasa jika route menggunakan Resource.
     */
    public function update(Request $request, $id)
    {
        return $this->updateStatusJurnal($request, $id);
    }
}
