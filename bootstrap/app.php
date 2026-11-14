<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CekSesiTunggalSiswa;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 1. Percayai proxy (Nginx / Cloudflare / aaPanel) agar HTTPS terbaca sempurna
        $middleware->trustProxies(at: '*');

        // 2. Bypass CSRF untuk rute login, logout & token
        $middleware->validateCsrfTokens(except: [
            'login',
            'logout',
            'siswa/login',
            'siswa/logout',
            'admin/token/*',
        ]);

        // 3. Daftarkan seluruh alias middleware
        $middleware->alias([
            'role'         => CheckRole::class,
            'single.siswa' => CekSesiTunggalSiswa::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();