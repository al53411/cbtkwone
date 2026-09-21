<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login CATKwOne</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen min-h-[100dvh] w-full bg-white flex font-sans overflow-x-hidden antialiased">

    <!-- ================= SPLASH SCREEN ================= -->
    <div id="splash-screen"
        class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-900/95 backdrop-blur-md transition-opacity duration-300 text-white opacity-100">
        <div class="flex flex-col items-center animate-pulse">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-blue-500/40">
                <i class="fa-solid fa-laptop-code text-3xl text-white"></i>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-wider mb-3">E-ADM CATKwOne</h2>
            <div class="w-8 h-8 border-4 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
        </div>
    </div>

    <div class="w-full min-h-screen grid grid-cols-1 lg:grid-cols-12">

        <!-- KIRI: Logo Utama Desktop -->
        <div class="hidden lg:flex lg:col-span-5 xl:col-span-6 items-center justify-center p-8 bg-slate-50">
            <img src="{{ asset('logo.png') }}" 
                 alt="CATKwOne SISTEM CAT SDN KAWU 1" 
                 class="max-w-md xl:max-w-lg w-full h-auto object-contain drop-shadow-xl hover:scale-105 transition-transform duration-300">
        </div>

        <!-- KANAN: Container Biru & Form Login -->
        <div class="lg:col-span-7 xl:col-span-6 bg-blue-600 lg:rounded-l-[3.5rem] flex items-center justify-center p-6 sm:p-12 relative overflow-hidden min-h-screen">

            <!-- Card Putih Form -->
            <div class="w-full max-w-sm sm:max-w-md bg-white rounded-3xl shadow-2xl p-8 sm:p-10 z-10 relative">

                <!-- Logo Tampil di Mobile / HP -->
                <div class="flex lg:hidden justify-center mb-6">
                    <img src="{{ asset('logo.png') }}" alt="Logo" class="w-36 h-auto object-contain">
                </div>

                <!-- Header Text -->
                <div class="mb-6 text-center sm:text-left">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-800 tracking-tight">Hello!</h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">Silakan masuk ke akun Anda</p>
                </div>

                <!-- Alert Error Laravel -->
                @if ($errors->any())
                <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-xs sm:text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Input Email / NIP -->
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-slate-400 text-sm pointer-events-none">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                            autocomplete="username"
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-full text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="Email Address / NIP">
                    </div>

                    <!-- Input Password -->
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-slate-400 text-sm pointer-events-none">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            class="w-full pl-11 pr-11 py-3.5 bg-slate-50/50 border border-slate-200 rounded-full text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="Password">
                        <button type="button" id="togglePassword" aria-label="Tampilkan password" class="absolute right-4 text-slate-400 hover:text-slate-600 focus:outline-none">
                            <i class="fa-solid fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between px-2 pt-1">
                        <label class="flex items-center text-xs text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 mr-2">
                            Ingat Saya
                        </label>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit"
                        class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white font-semibold rounded-full shadow-lg shadow-blue-500/30 transition-all duration-200 text-sm mt-2">
                        Login
                    </button>

                    <!-- Lupa Password Link -->
                    @if (Route::has('password.request'))
                    <div class="text-center pt-2">
                        <a href="{{ route('password.request') }}" class="text-xs text-slate-500 hover:text-blue-600 font-medium transition-colors">
                            Forgot Password?
                        </a>
                    </div>
                    @endif
                </form>

                <!-- Footer Include di Dalam Card -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    @include('partials.footer')
                </div>

            </div>

            <!-- Dekorasi Garis Melengkung -->
            <div class="absolute -bottom-24 -right-24 w-96 h-96 pointer-events-none opacity-40">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-white fill-none stroke-current stroke-[1.5]">
                    <circle cx="200" cy="200" r="160" />
                    <circle cx="200" cy="200" r="120" />
                    <circle cx="200" cy="200" r="80" />
                </svg>
            </div>

        </div>

    </div>

    <!-- Script Splash Screen & Toggle Password -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const splash = document.getElementById('splash-screen');
        if (splash) {
            setTimeout(() => {
                splash.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    splash.style.display = 'none';
                }, 300);
            }, 400);
        }

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (togglePassword && passwordInput && eyeIcon) {
            togglePassword.addEventListener('click', function() {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.classList.toggle('fa-eye', !isPassword);
                eyeIcon.classList.toggle('fa-eye-slash', isPassword);
            });
        }
    });
    </script>
</body>

</html>