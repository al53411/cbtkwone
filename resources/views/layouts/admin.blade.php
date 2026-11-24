<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard CAT')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @php
        $logoPath = public_path('logo.png');
        $logoUrl = file_exists($logoPath) ? asset('logo.png') . '?v=' . filemtime($logoPath) : asset('favicon.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $logoUrl }}">

    <style>
        .flat-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
        }

        .flat-input {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #1e293b;
            outline: none;
            transition: all 0.15s ease-in-out;
        }

        .flat-input:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-100 font-sans antialiased text-slate-800">

    <div class="flex h-screen overflow-hidden relative">

        <!-- SIDEBAR ADMIN CAT SEKOLAH -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-slate-950 text-slate-300 flex flex-col border-r border-slate-800 z-30 transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 ease-in-out h-full shrink-0">

            <!-- Header Sidebar: Info Sekolah -->
            <div class="h-16 flex items-center justify-between bg-slate-900 px-4 border-b border-slate-800 shrink-0">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <img src="{{ $logoUrl }}" alt="Logo" class="w-8 h-8 object-contain shrink-0">
                    <div class="flex flex-col truncate">
                        <span class="text-white font-bold text-sm tracking-wide truncate">
                            {{ Auth::user()->sekolah->nama_sekolah ?? $profilSekolah->nama_sekolah ?? 'Sistem Sekolah' }}
                        </span>
                        <span class="text-[10px] text-blue-400 font-medium">Admin Sekolah CAT</span>
                    </div>
                </div>
                <button type="button" onclick="toggleSidebar()"
                    class="md:hidden text-slate-400 hover:text-white focus:outline-none p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigasi Menu -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Menu Utama</p>

                <!-- Dashboard -->
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin/dashboard') }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Profil Sekolah -->
                <a href="{{ Route::has('admin.sekolah.index') ? route('admin.sekolah.index') : '#' }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.sekolah.*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-school w-5 text-center"></i>
                    <span>Profil Sekolah</span>
                </a>

                <!-- Input / Data Siswa -->
                <a href="{{ Route::has('admin.siswa.index') ? route('admin.siswa.index') : '#' }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.siswa.index') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-user-graduate w-5 text-center"></i>
                    <span>Input Data Siswa</span>
                </a>

                <!-- Cetak Kartu Ujian -->
                <a href="{{ Route::has('admin.siswa.cetak-kartu') ? route('admin.siswa.cetak-kartu') : (Route::has('admin.siswa.cetak-kartu-kelas') ? route('admin.siswa.cetak-kartu-kelas') : '#') }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.siswa.cetak-kartu*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-id-card w-5 text-center text-blue-400"></i>
                    <span>Cetak Kartu Ujian</span>
                </a>

                <!-- Data Korektor -->
                <a href="{{ Route::has('admin.korektor.index') ? route('admin.korektor.index') : url('/admin/korektor') }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.korektor.*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-user-pen w-5 text-center"></i>
                    <span>Data Korektor</span>
                </a>

                <div class="pt-3 pb-1">
                    <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Manajemen CAT</p>
                </div>

                <!-- Atur Sesi Ujian -->
                <a href="{{ Route::has('admin.sesi.index') ? route('admin.sesi.index') : '#' }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.sesi.*', 'admin.cat.sesi.*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                    <span>Atur Sesi Ujian</span>
                </a>

                <!-- Token Ujian (Dari Super Admin) -->
                <a href="{{ Route::has('admin.token.index') ? route('admin.token.index') : '#' }}"
                    class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.token.*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <div class="flex items-center space-x-3 truncate">
                        <i class="fa-solid fa-key w-5 text-center"></i>
                        <span class="truncate">Token Ujian CAT</span>
                    </div>
                    <span
                        class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        SuperAdmin
                    </span>
                </a>

                <!-- Daftar Paket Soal -->
                <a href="{{ Route::has('admin.soal.paket') ? route('admin.soal.paket') : '#' }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.soal.paket') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-layer-group w-5 text-center"></i>
                    <span>Daftar Paket Soal</span>
                </a>

                <!-- Monitoring Ujian -->
                <a href="{{ Route::has('admin.ujian.index') ? route('admin.ujian.index') : url('/admin/ujian') }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.ujian.index', 'admin.ujian.monitoring*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-desktop w-5 text-center"></i>
                    <span>Monitoring Ujian</span>
                </a>

                <!-- Hasil & Rekap Nilai -->
                <a href="{{ Route::has('admin.ujian.rekap') ? route('admin.ujian.rekap') : url('/admin/hasil-ujian') }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.ujian.rekap*', 'admin.ujian.hasil*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i class="fa-solid fa-clipboard-check w-5 text-center"></i>
                    <span>Hasil & Rekap Nilai</span>
                </a>

                <!-- Berita Acara Ujian CAT -->
                <a href="{{ Route::has('admin.ujian.berita-acara-index') ? route('admin.ujian.berita-acara-index') : '#' }}"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('admin.ujian.berita-acara*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                    <i
                        class="fa-solid fa-file-signature w-5 text-center {{ request()->routeIs('admin.ujian.berita-acara*') ? 'text-white' : 'text-amber-400' }}"></i>
                    <span>Berita Acara Ujian</span>
                </a>

            </div>

            <!-- Footer Sidebar (Tombol Logout) -->
            <div class="p-3 border-t border-slate-800 bg-slate-900/50 shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-400 bg-rose-500/10 hover:bg-rose-600 hover:text-white transition duration-150 cursor-pointer">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar / Logout</span>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-20 hidden md:hidden transition-opacity"></div>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Navbar Top -->
            <header
                class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-10 shrink-0 shadow-sm">
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="toggleSidebar()"
                        class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none md:hidden">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-slate-500 truncate">
                        <span class="font-medium text-slate-700 hidden sm:inline">Admin Sekolah</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 hidden sm:inline"></i>
                        <span class="text-slate-800 font-semibold truncate">@yield('page_title', 'Dashboard CAT')</span>
                    </div>

                </div>

                <!-- User Info & Indikator Tahun Ajaran -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    @php
                        $taAktif = class_exists(\App\Models\TahunAjaran::class)
                            ? \App\Models\TahunAjaran::getAktif(auth()->user()->sekolah_id ?? null)
                            : null;
                    @endphp

                    @if($taAktif)
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] sm:text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 shrink-0">
                            <i class="fa-solid fa-calendar-days mr-1.5 text-blue-500"></i>
                            <span class="hidden sm:inline">TP: </span>{{ $taAktif->tahun }} ({{ $taAktif->semester }})
                        </span>
                    @endif

                    <div class="flex items-center space-x-2 border-l border-slate-200 pl-2 sm:pl-3">
                        <span
                            class="text-xs sm:text-sm font-bold text-slate-700 hidden md:inline truncate max-w-[120px]">
                            {{ Auth::user()->name ?? 'Admin Sekolah' }}
                        </span>
                        <div
                            class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100">
                @yield('content')
            </main>
        </div>

    </div>

    @if(Auth::check() && Auth::user()->wajib_ganti_sandi)
        <!-- MODAL GANTI SANDI BAWAAN -->
        <div id="modalGantiSandi"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/75 backdrop-blur-xs p-4">
            <div class="bg-white w-full max-w-md rounded-2xl border border-slate-200 shadow-2xl overflow-hidden">
                <!-- Header Modal -->
                <div class="bg-amber-500 px-6 py-4 text-white flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-shield-halved text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wide">Ganti Kata Sandi Bawaan</h3>
                        <p class="text-[11px] text-amber-50">Demi keamanan akun sekolah, segera perbarui sandi dari
                            Superadmin.</p>
                    </div>
                </div>

                <!-- Form Ganti Sandi -->
                <form
                    action="{{ Route::has('admin.ganti-sandi-awal') ? route('admin.ganti-sandi-awal') : url('/admin/ganti-sandi-awal') }}"
                    method="POST" class="p-6 space-y-4">
                    @csrf

                    @if($errors->has('password'))
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                            {{ $errors->first('password') }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru</label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ulangi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" required minlength="8"
                            placeholder="Ketik ulang sandi baru"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modalGantiSandi').classList.add('hidden')"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition cursor-pointer">
                            Nanti Saja
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition cursor-pointer">
                            <i class="fa-solid fa-key mr-1"></i> Simpan Sandi Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Script Global UI Interaktif & Pop-up Cetak Kartu -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        // 1. Buka / Tutup Sidebar (Mobile)
        function toggleSidebar() {
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // 2. Pop-up Pilih Kelas untuk Cetak Kartu Massal
        function pilihKelasCetak() {
            Swal.fire({
                title: 'Cetak Kartu Ujian',
                text: 'Masukkan ID Kelas atau pilih metode cetak massal:',
                input: 'text',
                inputPlaceholder: 'Ketik ID Kelas...',
                showCancelButton: true,
                confirmButtonText: 'Cetak Berdasarkan Kelas',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#2563eb',
            }).then((result) => {
                if (result.value) {
                    const baseUrl = "{{ Route::has('admin.siswa.cetak-kartu') ? route('admin.siswa.cetak-kartu') : url('/admin/siswa/cetak-kartu') }}";
                    window.open(baseUrl + "?kelas_id=" + result.value, '_blank');
                }
            });
        }

        // 3. Resizing handler untuk responsivitas layar desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // 4. Alert Flash Notification (SweetAlert2)
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: @json(session('error'))
            });
        @endif
    </script>
    @stack('scripts')
</body>

</html>