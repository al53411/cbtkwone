<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Superadmin CAT Online')</title>
    
    <!-- CSS & Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <style>
        /* Custom Scrollbar */
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
            background: #10b981;
        }
    </style>
</head>

<body class="bg-slate-100 font-sans antialiased text-slate-800">

    <div class="flex h-screen overflow-hidden relative">

        <!-- SIDEBAR SUPERADMIN CAT / CBT -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-slate-950 text-slate-300 flex flex-col border-r border-slate-800 z-30 transform -translate-x-full md:translate-x-0 md:relative transition-transform duration-300 ease-in-out h-full">

            <!-- Header Sidebar -->
            <div class="h-16 flex items-center justify-between bg-slate-900 px-6 border-b border-slate-800 shrink-0">
                <div class="flex items-center space-x-3 truncate">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-lg shrink-0 shadow-sm">
                        <i class="fa-solid fa-laptop-code text-sm"></i>
                    </div>
                    <div class="flex flex-col truncate">
                        <span class="text-white font-bold text-sm tracking-wide uppercase truncate">CAT / CBT ONLINE</span>
                        <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-widest leading-none">Superadmin Center</span>
                    </div>
                </div>
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Navigasi Menu Sidebar -->
            <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
                
                <!-- MENU: PUSAT KONTROL -->
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pusat Kontrol</p>

                <a href="{{ route('superadmin.dashboard') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('superadmin.dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-lg shadow-emerald-900/30' : 'text-slate-400 hover:bg-slate-800/80 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-sm"></i>
                    <span class="text-xs">Dashboard Monitoring</span>
                </a>

                <!-- MENU: MANAJEMEN SEKOLAH & PROKTOR -->
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider pt-4 mb-2">Manajemen Instansi</p>
                
                @php
                    $isSekolahActive = request()->routeIs('superadmin.sekolah.*', 'superadmin.kepsek.*', 'superadmin.kelas.*');
                @endphp
                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-sekolah', 'arrow-sekolah')"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition group focus:outline-none {{ $isSekolahActive ? 'text-white font-medium bg-slate-900/60' : 'text-slate-400' }}">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-school w-5 text-center text-sm {{ $isSekolahActive ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }} transition"></i>
                            <span class="text-xs">Kelola Sekolah</span>
                        </div>
                        <i id="arrow-sekolah"
                            class="fa-solid fa-chevron-down text-[10px] text-slate-500 group-hover:text-white transition-transform duration-200 {{ $isSekolahActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="dropdown-sekolah"
                        class="{{ $isSekolahActive ? '' : 'hidden' }} pl-11 pr-2 py-1 space-y-1 bg-slate-900/40 rounded-xl">
                        <a href="{{ route('superadmin.sekolah.index') }}"
                            class="block py-2 px-3 text-xs rounded-lg transition {{ request()->routeIs('superadmin.sekolah.index') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-list-check text-[10px] mr-2"></i> Daftar Sekolah
                        </a>
                        <a href="{{ route('superadmin.kepsek.index') }}"
                            class="block py-2 px-3 text-xs rounded-lg transition {{ request()->routeIs('superadmin.kepsek.index') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-user-shield text-[10px] mr-2"></i> Proktor
                        </a>
                        <a href="{{ route('superadmin.kepsek.create') }}"
                            class="block py-2 px-3 text-xs rounded-lg transition {{ request()->routeIs('superadmin.kepsek.create') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-plus-circle text-[10px] mr-2"></i> Tambah Akun
                        </a>
                        <a href="{{ route('superadmin.kelas.index') }}"
                            class="block py-2 px-3 text-xs rounded-md transition {{ request()->routeIs('superadmin.kelas.*') ? 'text-blue-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-layer-group text-[10px] mr-2"></i> Data Kelas
                        </a>
                    </div>
                </div>

                <!-- MENU: MONITORING CAT / CBT -->
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider pt-4 mb-2">Pelaksanaan CAT</p>

                <a href="{{ route('superadmin.soal.index') }}""
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/80 hover:text-white transition">
                    <i class="fa-solid fa-tower-broadcast w-5 text-center text-sm text-amber-400"></i>
                    <span class="text-xs">Sesi Ujian Live</span>
                </a>

                <a href="{{ route('superadmin.soal.index') }}""
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/80 hover:text-white transition {{ request()->routeIs('superadmin.soal.index') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white' }}">
                    <i class="fa-solid fa-file-signature w-5 text-center text-sm text-indigo-400"></i>
                    <span class="text-xs">Master Bank Soal</span>
                </a>

                <a href="#"
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/80 hover:text-white transition">
                    <i class="fa-solid fa-square-poll-vertical w-5 text-center text-sm text-emerald-400"></i>
                    <span class="text-xs">Rekap & Nilai Hasil CAT</span>
                </a>

                <!-- MENU: SIMULASI & LINTAS ROLE -->
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider pt-4 mb-2">Akses & Simulasi</p>
                
                <a href="#"
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/80 hover:text-white transition">
                    <i class="fa-solid fa-user-gear w-5 text-center text-xs"></i>
                    <span class="text-xs">Mode Admin Sekolah</span>
                </a>
                <a href="#"
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/80 hover:text-white transition">
                    <i class="fa-solid fa-chalkboard-user w-5 text-center text-xs"></i>
                    <span class="text-xs">Mode Proktor / Guru</span>
                </a>
                <a href="#"
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/80 hover:text-white transition">
                    <i class="fa-solid fa-graduation-cap w-5 text-center text-xs"></i>
                    <span class="text-xs">Simulasi Ujian Siswa</span>
                </a>

            </div>

            <!-- Footer Sidebar (Tombol Logout) -->
            <div class="p-4 border-t border-slate-800 bg-slate-900/50 shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-rose-400 bg-rose-500/10 hover:bg-rose-600 hover:text-white transition duration-200 shadow-sm">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar dari CAT Pusat</span>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-20 hidden md:hidden"></div>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">

            <!-- Top Header / Navbar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10 shrink-0 shadow-xs">
                <div class="flex items-center space-x-4">
                    <button onclick="toggleSidebar()"
                        class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none md:hidden">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-gray-500">
                        <span class="font-bold text-emerald-600">CAT ONLINE</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
                        <span class="font-medium text-gray-700"> 
                            @hasSection('header') @yield('header')
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
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Superadmin CAT Center</span>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-emerald-400 border border-slate-700 flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Main Content Section -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
                @yield('content')
            </main>
        </div>

    </div>

    <!-- SCRIPT INTERAKTIF & SWAL NOTIFICATION -->
    <script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

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
        customClass: { popup: 'rounded-2xl' }
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Terjadi Kesalahan',
        text: @json(session('error')),
        customClass: { popup: 'rounded-2xl' }
    });
    @endif
    </script>
</body>

</html>