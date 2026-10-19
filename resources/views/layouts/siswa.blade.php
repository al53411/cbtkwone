<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ujian CBT Siswa')</title>
    <!-- Tailwind CSS / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
</head>
<body class="bg-slate-100 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Navbar Minimalis (Hanya Nama Aplikasi / Status Logout Sederhana) -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-xs">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                CBT
            </div>
            <div>
                <span class="text-xs font-bold text-slate-800 block">CBT Online Siswa</span>
                <span class="text-[10px] text-slate-500">Mode Ujian Terkunci</span>
            </div>
        </div>

        <div>
            <form action="{{ route('siswa.logout') }}" method="POST" onsubmit="return confirm('Keluar dari ujian akan mengakhiri sesi. Yakin?')">
                @csrf
                <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition">
                    <i class="fa-solid fa-power-off mr-1"></i> Keluar
                </button>
            </form>
        </div>
    </header>

    <!-- Konten Utama Full Width -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-8">
        @yield('content')
    </main>

    <!-- Footer Minimalis -->
    <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200 bg-white">
        &copy; {{ date('Y') }} Sistem CBT Sekolah. Hak Cipta Dilindungi.
    </footer>

</body>
</html>