<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Superadmin CAT Online')</title>

    <!-- CSS & Libraries -->
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

        /* Spinner Berwarna Gradasi */
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

    <!-- Global Page Transition Color Spinner Overlay -->
    <div id="globalPageLoader"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 transition-opacity">
        <div class="bg-slate-100 border border-slate-300 rounded-md px-6 py-5 flex flex-col items-center gap-3">
            <div class="color-spinner"></div>
            <span id="globalLoaderText" class="text-xs font-bold text-slate-700 tracking-wide">Memuat halaman...</span>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden relative">

        <!-- SIDEBAR SUPERADMIN CAT / CBT (FLAT SOFT SLATE & BIRU) -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-slate-100 text-slate-700 flex flex-col border-r border-slate-300 z-30 transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 ease-in-out h-full shrink-0">

            <!-- Header Sidebar (Biru Cerah dengan Bayangan Bawah) -->
            <div
                class="h-16 flex items-center justify-between bg-blue-600 px-4 border-b border-blue-700 shadow-md relative z-10 shrink-0">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-md bg-slate-100 p-1 flex items-center justify-center shrink-0">
                        <img src="{{ $logoUrl }}" alt="Logo" class="w-full h-full object-contain">
                    </div>

                    <!-- Informasi Akun -->
                    <div class="flex flex-col truncate">
                        <span class="text-sm font-bold text-white tracking-wide truncate">
                            {{ Auth::user()->name ?? 'Nama Pengguna' }}
                        </span>
                        <span class="text-[10px] text-blue-100 font-medium uppercase truncate">
                            {{ Auth::user()->role ?? 'Superadmin' }}
                        </span>
                    </div>
                </div>
                <button type="button" onclick="toggleSidebar()"
                    class="md:hidden text-blue-100 hover:text-white focus:outline-none p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigasi Menu Sidebar -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 bg-slate-100">

                <!-- ========================================== -->
                <!-- KELOMPOK 1: PUSAT MONITORING               -->
                <!-- ========================================== -->
                @php
                    $isUjianLiveActive = request()->routeIs('superadmin.ujian.*') && !request()->routeIs('superadmin.ujian.rekap*', 'superadmin.ujian.cetak-rekap*');
                    $isRekapActive = request()->routeIs('superadmin.ujian.rekap*', 'superadmin.ujian.cetak-rekap*');
                    $isJadwalActive = request()->routeIs('superadmin.jadwal.*');
                @endphp
                <div>
                    <p class="px-3 text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2">Pusat Monitoring
                    </p>
                    <div class="space-y-1">
                        <a href="{{ route('superadmin.dashboard') }}"
                            class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('superadmin.dashboard') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                            <i
                                class="fa-solid fa-chart-pie w-5 text-center {{ request()->routeIs('superadmin.dashboard') ? 'text-white' : 'text-blue-600' }}"></i>
                            <span>Dashboard Utama</span>
                        </a>

                        <a href="{{ route('superadmin.ujian.index') }}"
                            class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ $isUjianLiveActive ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                            <i
                                class="fa-solid fa-tower-broadcast w-5 text-center {{ $isUjianLiveActive ? 'text-white' : 'text-amber-600' }}"></i>
                            <span>Ujian Live Realtime</span>
                        </a>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- KELOMPOK 2: DATA INSTANSI & USER           -->
                <!-- ========================================== -->
                <div>
                    <p class="px-3 text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2">Data Instansi
                        &amp; User</p>
                    <div class="space-y-1">

                        <!-- Dropdown Kelola Sekolah -->
                        @php
                            $isSekolahActive = request()->routeIs('superadmin.sekolah.*', 'superadmin.kelas.*');
                        @endphp
                        <div>
                            <button type="button" onclick="toggleDropdown('dropdown-sekolah', 'arrow-sekolah')"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-md text-sm transition group focus:outline-none {{ $isSekolahActive ? 'bg-blue-100/80 text-blue-800 font-semibold border border-blue-200' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                                <div class="flex items-center space-x-3">
                                    <i
                                        class="fa-solid fa-school w-5 text-center {{ $isSekolahActive ? 'text-blue-700' : 'text-blue-600' }} transition"></i>
                                    <span>Sekolah &amp; Kelas</span>
                                </div>
                                <i id="arrow-sekolah"
                                    class="fa-solid fa-chevron-down text-[10px] text-slate-500 transition-transform duration-200 {{ $isSekolahActive ? 'rotate-180' : '' }}"></i>
                            </button>

                            <div id="dropdown-sekolah"
                                class="{{ $isSekolahActive ? '' : 'hidden' }} pl-8 pr-1 py-1.5 space-y-1 bg-slate-200/60 border border-slate-200 rounded-md mt-1">
                                <a href="{{ route('superadmin.sekolah.index') }}"
                                    class="nav-link-loader block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('superadmin.sekolah.index') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/80 hover:text-blue-800' }}">
                                    <i class="fa-solid fa-list-check text-[10px] mr-2"></i> Daftar Sekolah
                                </a>
                                <a href="{{ route('superadmin.kelas.index') }}"
                                    class="nav-link-loader block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('superadmin.kelas.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/80 hover:text-blue-800' }}">
                                    <i class="fa-solid fa-layer-group text-[10px] mr-2"></i> Data Kelas
                                </a>
                            </div>
                        </div>

                        <!-- Dropdown Kelola Akun Proktor / Kepsek -->
                        @php
                            $isKepsekActive = request()->routeIs('superadmin.kepsek.*');
                        @endphp
                        <div>
                            <button type="button" onclick="toggleDropdown('dropdown-proktor', 'arrow-proktor')"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-md text-sm transition group focus:outline-none {{ $isKepsekActive ? 'bg-blue-100/80 text-blue-800 font-semibold border border-blue-200' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                                <div class="flex items-center space-x-3">
                                    <i
                                        class="fa-solid fa-user-shield w-5 text-center {{ $isKepsekActive ? 'text-blue-700' : 'text-blue-600' }} transition"></i>
                                    <span>Akun Proktor / Admin</span>
                                </div>
                                <i id="arrow-proktor"
                                    class="fa-solid fa-chevron-down text-[10px] text-slate-500 transition-transform duration-200 {{ $isKepsekActive ? 'rotate-180' : '' }}"></i>
                            </button>

                            <div id="dropdown-proktor"
                                class="{{ $isKepsekActive ? '' : 'hidden' }} pl-8 pr-1 py-1.5 space-y-1 bg-slate-200/60 border border-slate-200 rounded-md mt-1">
                                <a href="{{ route('superadmin.kepsek.index') }}"
                                    class="nav-link-loader block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('superadmin.kepsek.index') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/80 hover:text-blue-800' }}">
                                    <i class="fa-solid fa-users text-[10px] mr-2"></i> Daftar Proktor
                                </a>
                                <a href="{{ route('superadmin.kepsek.create') }}"
                                    class="nav-link-loader block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('superadmin.kepsek.create') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/80 hover:text-blue-800' }}">
                                    <i class="fa-solid fa-user-plus text-[10px] mr-2"></i> Tambah Akun Baru
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ========================================== -->
                <!-- KELOMPOK 3: BANK SOAL & MATERI             -->
                <!-- ========================================== -->
                <div>
                    <p class="px-3 text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2">Bank Soal &amp;
                        Materi</p>
                    <div class="space-y-1">
                        <a href="{{ route('superadmin.mapel.index') }}"
                            class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('superadmin.mapel.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                            <i
                                class="fa-solid fa-book-bookmark w-5 text-center {{ request()->routeIs('superadmin.mapel.*') ? 'text-white' : 'text-sky-600' }}"></i>
                            <span>Mata Pelajaran</span>
                        </a>
                        <a href="{{ route('superadmin.soal.index') }}"
                            class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('superadmin.soal.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                            <i
                                class="fa-solid fa-file-signature w-5 text-center {{ request()->routeIs('superadmin.soal.*') ? 'text-white' : 'text-indigo-600' }}"></i>
                            <span>Master Bank Soal</span>
                        </a>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- KELOMPOK 4: HASIL & LAPORAN                -->
                <!-- ========================================== -->
                <div>
                    <p class="px-3 text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2">Hasil &amp;
                        Laporan</p>
                    <div class="space-y-1">
                        <a href="{{ route('superadmin.ujian.rekap') }}"
                            class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ $isRekapActive ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                            <i
                                class="fa-solid fa-clipboard-check w-5 text-center {{ $isRekapActive ? 'text-white' : 'text-emerald-600' }}"></i>
                            <span>Rekap &amp; Rilis Nilai</span>
                        </a>

                        <a href="{{ route('superadmin.jadwal.index') }}"
                            class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ $isJadwalActive ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                            <i
                                class="fa-solid fa-calendar-days w-5 text-center {{ $isJadwalActive ? 'text-white' : 'text-sky-600' }}"></i>
                            <span>Jadwal Ujian</span>
                        </a>

                        <a href="{{ route('superadmin.berita-acara.index') }}"
                            class="nav-link-loader flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm transition {{ request()->routeIs('superadmin.berita-acara.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-700 hover:bg-blue-100/70 hover:text-blue-800' }}">
                            <i
                                class="fa-solid fa-file-shield w-5 text-center {{ request()->routeIs('superadmin.berita-acara.*') ? 'text-white' : 'text-blue-600' }}"></i>
                            <span>Arsip Berita Acara</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Footer Sidebar (Tombol Logout) -->
            <div class="p-3 border-t border-slate-300 bg-slate-200/70 shrink-0">
                <form method="POST" action="{{ route('logout') }}" onsubmit="showPageLoader('Keluar dari sistem...')">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-md text-xs font-semibold text-rose-700 bg-slate-100 border border-rose-300 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition duration-150 cursor-pointer">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar dari CAT Pusat</span>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Sidebar Overlay (Mobile - Flat) -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-900/40 z-20 hidden md:hidden transition-opacity"></div>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">

            <!-- Top Header / Navbar (Flat Soft Slate & Biru) -->
            <header
                class="h-16 bg-slate-100 border-b border-slate-300 flex items-center justify-between px-4 sm:px-6 z-10 shrink-0">
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="toggleSidebar()"
                        class="p-2 rounded-md text-blue-600 hover:bg-blue-100 focus:outline-none md:hidden">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-slate-600 truncate">
                        <span class="font-bold text-blue-600">CAT ONLINE</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        <span class="font-semibold text-slate-800 truncate">
                            @hasSection('header')
                                @yield('header')
                            @else
                                @yield('title', 'Dashboard Monitoring')
                            @endif
                        </span>
                    </div>
                </div>

                <!-- User Info -->
                <div class="flex items-center space-x-3">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-xs font-bold text-slate-800 leading-tight">
                            {{ Auth::user()->name ?? 'Super Admin' }}
                        </span>
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Superadmin CAT
                            Center</span>
                    </div>
                    <div
                        class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Main Content Section -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 bg-slate-200/75">
                @yield('content')
            </main>
        </div>

    </div>

    <!-- SCRIPT INTERAKTIF, LOADER & SWAL NOTIFICATION -->
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

        // Aktifkan spinner saat berpindah menu sidebar
        document.querySelectorAll('.nav-link-loader').forEach(link => {
            link.addEventListener('click', function () {
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

        // Sembunyikan spinner saat navigasi Back browser
        window.addEventListener('pageshow', () => hidePageLoader());

        function toggleSidebar() {
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function toggleDropdown(id, arrowId) {
            const dropdown = document.getElementById(id);
            const arrow = document.getElementById(arrowId);

            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                dropdown.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // Alert Flash Notification via SweetAlert2
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 2000,
                customClass: { popup: 'rounded-md' }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: @json(session('error')),
                customClass: { popup: 'rounded-md' }
            });
        @endif
    </script>
    @stack('scripts')
</body>

</html>