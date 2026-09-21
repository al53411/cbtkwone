<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Superadmin CBT ONLINE')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <style>
    /* Kotak Data & Tabel Solid Flat */
    .flat-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
    }

    /* Tombol Dropdown Sidebar Flat */
    .flat-dropdown {
        background-color: #0f172a;
        border-left: 3px solid #10b981;
    }

    /* Kolom Input Form Flat Sempurna */
    .flat-input {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        color: #1e293b;
        outline: none;
        transition: border-color 0.15s ease-in-out;
    }

    .flat-input:focus {
        background-color: #ffffff;
        border-color: #10b981;
    }

    /* Scrollbar Custom Flat */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 0px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    </style>
</head>

<body class="bg-slate-100 font-sans antialiased text-slate-800">

    <div class="flex h-screen overflow-hidden relative">

        <!-- SIDEBAR SUPERADMIN CBT ONLINE -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 w-64 bg-slate-950 text-slate-300 flex flex-col border-r border-slate-800 z-30 transform -translate-x-full md:translate-x-0 md:relative transition-transform duration-300 ease-in-out h-full">

            <!-- Header Sidebar CBT ONLINE -->
            <div class="h-16 flex items-center justify-between bg-slate-900 px-6 border-b border-slate-800 shrink-0">
                <div class="flex items-center space-x-3 truncate">
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="w-7 h-7 object-contain shrink-0">
                    <div class="flex flex-col truncate">
                        <span class="text-white font-bold text-sm tracking-wide uppercase truncate">CBT ONLINE</span>
                        <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-widest leading-none">Superadmin</span>
                    </div>
                </div>
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Navigasi Menu Superadmin -->
            <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
                <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pusat Kontrol</p>

                <!-- Dashboard Pusat -->
                <a href="{{ route('superadmin.dashboard') }}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('superadmin.dashboard') ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span class="text-sm">Dashboard Pusat</span>
                </a>

                <!-- Kelola Sekolah & Kepsek -->
                @php
                $isSekolahActive = request()->routeIs('superadmin.sekolah.*', 'superadmin.kepsek.*');
                @endphp
                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-sekolah', 'arrow-sekolah')"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-slate-800 hover:text-white transition group focus:outline-none {{ $isSekolahActive ? 'text-white font-medium' : '' }}">
                        <div class="flex items-center space-x-3">
                            <i
                                class="fa-solid fa-building-columns w-5 text-center {{ $isSekolahActive ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }} transition"></i>
                            <span class="font-medium text-sm">Kelola Sekolah</span>
                        </div>
                        <i id="arrow-sekolah"
                            class="fa-solid fa-chevron-down text-xs text-slate-500 group-hover:text-white transition-transform duration-200 {{ $isSekolahActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="dropdown-sekolah"
                        class="{{ $isSekolahActive ? '' : 'hidden' }} pl-11 pr-2 py-1 space-y-1 bg-slate-900/40 rounded-lg">
                        <a href="{{ route('superadmin.sekolah.index') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('superadmin.sekolah.index') ? 'text-emerald-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-school text-xs mr-2"></i> Daftar Sekolah
                        </a>
                        <a href="{{ route('superadmin.kepsek.index') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('superadmin.kepsek.index') ? 'text-emerald-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-user-tie text-xs mr-2"></i> Data Kepala Sekolah
                        </a>
                        <a href="{{ route('superadmin.kepsek.create') }}"
                            class="block py-2 px-3 text-sm rounded-md transition {{ request()->routeIs('superadmin.kepsek.create') ? 'text-emerald-400 font-semibold bg-slate-800/50' : 'text-slate-400 hover:text-white' }}">
                            <i class="fa-solid fa-user-plus text-xs mr-2"></i> Tambah Kepsek / Sekolah
                        </a>
                    </div>
                </div>

                <!-- Monitoring Lintas Role -->
                <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider pt-4 mb-2">Monitoring Lintas Role</p>
                <a href="#"
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-user-shield w-5 text-center text-xs"></i>
                    <span class="text-xs">Lihat Mode Admin</span>
                </a>
                <a href="#"
                    class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-chalkboard-user w-5 text-center text-xs"></i>
                    <span class="text-xs">Lihat Mode Guru</span>
                </a>

            </div>

            <!-- Footer Sidebar (Tombol Logout) -->
            <div class="p-4 border-t border-slate-800 bg-slate-900/50 shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 rounded-lg text-sm font-medium text-rose-400 bg-rose-500/10 hover:bg-rose-600 hover:text-white transition duration-150 ease-in-out">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar / Logout</span>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-20 hidden md:hidden"></div>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">

            <!-- Navbar Top -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10 shrink-0">
                <div class="flex items-center space-x-4">
                    <button onclick="toggleSidebar()"
                        class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none md:hidden">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span class="font-semibold text-emerald-600">CBT ONLINE</span>
                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                        <span class="text-gray-600">@yield('page_title', 'Dashboard Pusat')</span>
                    </div>
                </div>

                <!-- User Info Ringkas -->
                <div class="flex items-center space-x-3">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-700 leading-tight">
                            {{ Auth::user()->name ?? 'Super Admin' }}
                        </span>
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Superadmin CBT</span>
                    </div>
                    <div
                        class="w-9 h-9 rounded-full bg-slate-900 text-emerald-400 border border-slate-700 flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                @yield('content')
            </main>
        </div>

    </div>

    <script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    // 1. Fungsi Buka / Tutup Sidebar
    function toggleSidebar() {
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    // 2. Fungsi Buka / Tutup Dropdown Menu
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

    // 3. Resizing handler untuk layar desktop
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
</body>

</html>