<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard CAT')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @php
        $logoPath = public_path('logo.png');
        $logoUrl = file_exists($logoPath) ? asset('logo.png') . '?v=' . filemtime($logoPath) : asset('favicon.png');
        $currentUser = Auth::user();
        $userRole = strtolower($currentUser->role ?? 'admin');
        $isPetugasUjian = in_array($userRole, ['proktor', 'pengawas', 'korektor']);
    @endphp
    <link rel="icon" type="image/png" href="{{ $logoUrl }}">

    <style>
        .flat-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
        }

        .flat-input {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            color: #1e293b;
            outline: none;
            transition: all 0.15s ease-in-out;
        }

        .flat-input:focus {
            background-color: #ffffff;
            border-color: #2563eb;
        }

        /* Spinner Berwarna Gradasi (Biru - Indigo - Emerald) */
        .color-spinner {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #3b82f6, #6366f1, #10b981, #f59e0b, #3b82f6);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 5px), #000 0);
            mask: radial-gradient(farthest-side, transparent calc(100% - 5px), #000 0);
            animation: spinRing 0.85s linear infinite;
        }

        @keyframes spinRing {
            to {
                transform: rotate(360deg);
            }
        }

        /* Top Progress Bar Animasi */
        .top-loader-bar {
            background: linear-gradient(90deg, #6d7a8f, #3c3c5c, #10b981, #f59e0b);
            background-size: 200% 100%;
            animation: shimmerBar 1.2s linear infinite;
        }

        @keyframes shimmerBar {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Scrollbar Flat Soft Gray-Blue */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #e2e8f0;
        }

        ::-webkit-scrollbar-thumb {
            background: #798597;
            border-radius: 2px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #5b6676;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-200/75 font-sans antialiased text-slate-800">

    <!-- Top Loading Progress Bar -->
    <div id="topProgressBar" class="fixed top-0 left-0 right-0 h-1 top-loader-bar z-50 hidden"></div>

    <!-- Global Page Transition Color Spinner Overlay (Flat Soft Gray & Biru) -->
    <div id="globalPageLoader"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 transition-opacity">
        <div class="bg-slate-100 border border-slate-300 rounded-md px-6 py-5 flex flex-col items-center gap-3">
            <div class="color-spinner"></div>
            <span id="globalLoaderText" class="text-xs font-bold text-slate-700 tracking-wide">Memuat halaman...</span>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden relative">

        <!-- SIDEBAR TERPADU ADMIN & PETUGAS UJIAN (FLAT SOFT WHITE/SLATE & BIRU) -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-slate-100 text-slate-700 flex flex-col border-r border-slate-300 z-30 transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 ease-in-out h-full shrink-0">

            <!-- Header Sidebar: Info Sekolah (Biru Cerah dengan Bayangan Bawah) -->
            <div
                class="h-16 flex items-center justify-between bg-blue-600 px-4 border-b border-blue-700 shadow-md relative z-10 shrink-0">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-md bg-slate-100 p-1 flex items-center justify-center shrink-0">
                        <img src="{{ $logoUrl }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col truncate">
                        <span class="text-white font-bold text-sm tracking-wide truncate">
                            {{ $currentUser->sekolah->nama_sekolah ?? $profilSekolah->nama_sekolah ?? 'Sistem Sekolah' }}
                        </span>
                        <span class="text-[10px] text-blue-100 font-medium uppercase">
                            {{ $isPetugasUjian ? 'Petugas CAT (' . $userRole . ')' : 'Admin Sekolah CAT' }}
                        </span>
                    </div>
                </div>
                <button type="button" onclick="toggleSidebar()"
                    class="md:hidden text-blue-100 hover:text-white focus:outline-none p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigasi Menu -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1 bg-slate-100">
                @if(!$isPetugasUjian)
                    <p class="px-3 text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2">Menu Utama</p>

                    <!-- Dashboard -->
                    <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin/dashboard') }}"
                        class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <i
                            class="fa-solid fa-chart-pie w-5 text-center {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-blue-600' }}"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Profil Sekolah -->
                    <a href="{{ Route::has('admin.sekolah.index') ? route('admin.sekolah.index') : '#' }}"
                        class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.sekolah.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <i
                            class="fa-solid fa-school w-5 text-center {{ request()->routeIs('admin.sekolah.*') ? 'text-white' : 'text-blue-600' }}"></i>
                        <span>Profil Sekolah</span>
                    </a>

                    <!-- Input / Data Siswa -->
                    <a href="{{ Route::has('admin.siswa.index') ? route('admin.siswa.index') : '#' }}"
                        class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.siswa.index') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <i
                            class="fa-solid fa-user-graduate w-5 text-center {{ request()->routeIs('admin.siswa.index') ? 'text-white' : 'text-blue-600' }}"></i>
                        <span>Input Data Siswa</span>
                    </a>

                    <!-- Cetak Kartu Ujian -->
                    <a href="{{ Route::has('admin.siswa.cetak-kartu') ? route('admin.siswa.cetak-kartu') : (Route::has('admin.siswa.cetak-kartu-kelas') ? route('admin.siswa.cetak-kartu-kelas') : '#') }}"
                        class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.siswa.cetak-kartu*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <i
                            class="fa-solid fa-id-card w-5 text-center {{ request()->routeIs('admin.siswa.cetak-kartu*') ? 'text-white' : 'text-blue-600' }}"></i>
                        <span>Cetak Kartu Ujian</span>
                    </a>

                    <div class="pt-3 pb-1">
                        <p class="px-3 text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2">Manajemen CAT</p>
                    </div>

                    <!-- Atur Sesi Ujian -->
                    <a href="{{ Route::has('admin.sesi.index') ? route('admin.sesi.index') : '#' }}"
                        class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.sesi.*', 'admin.cat.sesi.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <i
                            class="fa-solid fa-clock-rotate-left w-5 text-center {{ request()->routeIs('admin.sesi.*', 'admin.cat.sesi.*') ? 'text-white' : 'text-blue-600' }}"></i>
                        <span>Atur Sesi Ujian</span>
                    </a>

                    <!-- Token Ujian (Dari Super Admin) -->
                    <a href="{{ Route::has('admin.token.index') ? route('admin.token.index') : '#' }}"
                        class="nav-link-loader flex items-center justify-between px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.token.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <div class="flex items-center space-x-3 truncate">
                            <i
                                class="fa-solid fa-key w-5 text-center {{ request()->routeIs('admin.token.*') ? 'text-white' : 'text-blue-600' }}"></i>
                            <span class="truncate">Token Ujian CAT</span>
                        </div>
                    </a>

                    <!-- Daftar Paket Soal -->
                    <a href="{{ Route::has('admin.soal.paket') ? route('admin.soal.paket') : '#' }}"
                        class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.soal.paket') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <i
                            class="fa-solid fa-layer-group w-5 text-center {{ request()->routeIs('admin.soal.paket') ? 'text-white' : 'text-blue-600' }}"></i>
                        <span>Daftar Paket Soal</span>
                    </a>

                    <!-- Hasil & Rekap Nilai -->
                    <a href="{{ Route::has('admin.ujian.rekap') ? route('admin.ujian.rekap') : url('/admin/hasil-ujian') }}"
                        class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.ujian.rekap*', 'admin.ujian.hasil*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <i
                            class="fa-solid fa-clipboard-check w-5 text-center {{ request()->routeIs('admin.ujian.rekap*', 'admin.ujian.hasil*') ? 'text-white' : 'text-blue-600' }}"></i>
                        <span>Hasil & Rekap Nilai</span>
                    </a>
                @endif

                <!-- KELOMPOK SATU SIDEBAR: PROKTOR, PENGAWAS & KOREKTOR -->
                <div class="{{ !$isPetugasUjian ? 'pt-3 pb-1' : 'pb-1' }}">
                    <p class="px-3 text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2">
                        Proktor, Pengawas &amp; Korektor
                    </p>
                </div>

                @if(!$isPetugasUjian || $userRole === 'proktor')
                    <!-- Proktor: Kelola Data Korektor / Petugas & Monitoring Ujian -->
                    <a href="{{ Route::has('admin.korektor.index') ? route('admin.korektor.index') : url('/admin/korektor') }}"
                        class="nav-link-loader flex items-center justify-between px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.korektor.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <div class="flex items-center space-x-3 truncate">
                            <i
                                class="fa-solid fa-user-gear w-5 text-center {{ request()->routeIs('admin.korektor.*') ? 'text-white' : 'text-sky-600' }}"></i>
                            <span class="truncate">Korektor</span>
                        </div>
                    </a>
                @endif

                @if(!$isPetugasUjian || in_array($userRole, ['proktor', 'pengawas']))
                    <!-- Pengawas: Monitoring Ujian & Berita Acara -->
                    <a href="{{ Route::has('admin.ujian.index') ? route('admin.ujian.index') : url('/admin/ujian') }}"
                        class="nav-link-loader flex items-center justify-between px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.ujian.index', 'admin.ujian.monitoring*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <div class="flex items-center space-x-3 truncate">
                            <i
                                class="fa-solid fa-desktop w-5 text-center {{ request()->routeIs('admin.ujian.index', 'admin.ujian.monitoring*') ? 'text-white' : 'text-blue-600' }}"></i>
                            <span class="truncate">Monitoring Ujian</span>
                        </div>
                    </a>

                    <a href="{{ Route::has('admin.ujian.berita-acara-index') ? route('admin.ujian.berita-acara-index') : '#' }}"
                        class="nav-link-loader flex items-center justify-between px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('admin.ujian.berita-acara*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                        <div class="flex items-center space-x-3 truncate">
                            <i
                                class="fa-solid fa-file-signature w-5 text-center {{ request()->routeIs('admin.ujian.berita-acara*') ? 'text-white' : 'text-blue-600' }}"></i>
                            <span class="truncate">Berita Acara Ujian</span>
                        </div>
                    </a>
                @endif

            </div>

            <!-- Footer Sidebar (Tombol Logout) -->
            <div class="p-3 border-t border-slate-300 bg-slate-200/70 shrink-0">
                <form method="POST" action="{{ route('logout') }}" onsubmit="showPageLoader('Keluar dari sistem...')">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-md text-xs font-semibold text-rose-700 bg-slate-100 border border-rose-300 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition duration-150 cursor-pointer">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Sidebar Overlay (Mobile - Flat) -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-900/40 z-20 hidden md:hidden transition-opacity"></div>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Navbar Top (Flat Soft Slate & Biru - Tanpa Shadow) -->
            <header
                class="h-16 bg-slate-100 border-b border-slate-300 flex items-center justify-between px-4 sm:px-6 z-10 shrink-0">
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="toggleSidebar()"
                        class="p-2 rounded-md text-blue-600 hover:bg-blue-100 focus:outline-none md:hidden">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-slate-600 truncate">
                        <span class="font-semibold text-blue-600 hidden sm:inline">
                            {{ $isPetugasUjian ? ucfirst($userRole) : 'Admin Sekolah' }}
                        </span>
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
                            class="inline-flex items-center px-2.5 py-1 rounded-sm text-[11px] sm:text-xs font-semibold bg-blue-100/80 text-blue-800 border border-blue-300 shrink-0">
                            <i class="fa-solid fa-calendar-days mr-1.5 text-blue-600"></i>
                            <span class="hidden sm:inline">TP: </span>{{ $taAktif->tahun }} ({{ $taAktif->semester }})
                        </span>
                    @endif

                    <div class="flex items-center space-x-2 border-l border-slate-300 pl-2 sm:pl-3">
                        <span
                            class="text-xs sm:text-sm font-bold text-slate-700 hidden md:inline truncate max-w-[120px]">
                            {{ $currentUser->name ?? 'Admin Sekolah' }}
                        </span>
                        <div
                            class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr($currentUser->name ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-200/75">
                @yield('content')
            </main>
        </div>

    </div>

    @if(Auth::check() && Auth::user()->wajib_ganti_sandi)
        <!-- MODAL GANTI SANDI BAWAAN (FLAT SOFT SLATE & BIRU) -->
        <div id="modalGantiSandi" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-slate-100 w-full max-w-md rounded-md border border-slate-300 overflow-hidden">
                <!-- Header Modal -->
                <div class="bg-blue-600 px-6 py-4 text-white flex items-center gap-3 border-b border-blue-700">
                    <div class="w-10 h-10 rounded-md bg-blue-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-shield-halved text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wide">Ganti Kata Sandi Bawaan</h3>
                        <p class="text-[11px] text-blue-100">Demi keamanan akun sekolah, segera perbarui sandi dari
                            Superadmin.</p>
                    </div>
                </div>

                <!-- Form Ganti Sandi -->
                <form
                    action="{{ Route::has('admin.ganti-sandi-awal') ? route('admin.ganti-sandi-awal') : url('/admin/ganti-sandi-awal') }}"
                    method="POST" class="p-6 space-y-4" onsubmit="showPageLoader('Menyimpan sandi baru...')">
                    @csrf

                    @if($errors->has('password'))
                        <div class="p-3 rounded-sm bg-rose-100 border border-rose-300 text-rose-800 text-xs font-semibold">
                            {{ $errors->first('password') }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru</label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                            class="w-full rounded-sm bg-white border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ulangi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" required minlength="8"
                            placeholder="Ketik ulang sandi baru"
                            class="w-full rounded-sm bg-white border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-600 focus:outline-none">
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modalGantiSandi').classList.add('hidden')"
                            class="px-4 py-2 rounded-sm bg-slate-200 border border-slate-300 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer">
                            Nanti Saja
                        </button>
                        <button type="submit"
                            class="px-5 py-2 rounded-sm bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition cursor-pointer">
                            <i class="fa-solid fa-key mr-1"></i> Simpan Sandi Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Script Global UI Interaktif, Spinner & Pop-up Cetak Kartu -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const globalLoader = document.getElementById('globalPageLoader');
        const globalLoaderText = document.getElementById('globalLoaderText');
        const topProgressBar = document.getElementById('topProgressBar');

        function showPageLoader(text = 'Memuat halaman...') {
            if (globalLoaderText) globalLoaderText.textContent = text;
            if (topProgressBar) topProgressBar.classList.remove('hidden');
            if (globalLoader) {
                globalLoader.classList.remove('hidden');
                globalLoader.classList.add('flex');
            }
        }

        function hidePageLoader() {
            if (topProgressBar) topProgressBar.classList.add('hidden');
            if (globalLoader) {
                globalLoader.classList.add('hidden');
                globalLoader.classList.remove('flex');
            }
        }

        // Aktifkan spinner berwarna saat berpindah menu sidebar
        document.querySelectorAll('.nav-link-loader').forEach(link => {
            link.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (!href || href === '#' || href.startsWith('javascript:') || this.target === '_blank') return;
                showPageLoader('Membuka ' + this.textContent.trim() + '...');
            });
        });

        // Aktifkan spinner saat form disubmit
        document.querySelectorAll('main form').forEach(form => {
            form.addEventListener('submit', function () {
                if (this.dataset.noLoader === 'true') return;
                showPageLoader('Memproses data...');
            });
        });

        // Pastikan spinner otomatis hilang jika pengguna menekan tombol Back browser
        window.addEventListener('pageshow', () => hidePageLoader());

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