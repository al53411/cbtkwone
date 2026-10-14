<?php

// Jalankan trik /tmp HANYA jika server berjalan di environment Vercel
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL_ENV'])) {
    
    // 1. Buat folder temporary di /tmp untuk Vercel Serverless
    $directories = [
        '/tmp/storage/app/public',
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/framework/sessions',
        '/tmp/bootstrap/cache',
    ];

    foreach ($directories as $directory) {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    // 2. Set Environment Path Cache ke /tmp
    putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
    putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
    putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
    putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
    putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
    putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');

    // 3. Load Autoload & Application
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // 4. Custom Storage Path
    $app->useStoragePath('/tmp/storage');

    // 5. Normalisasi Script Name untuk Vercel Rewrites
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

    // 6. Jalankan Kernel Request
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    $response->send();
    $kernel->terminate($request, $response);

} else {
    // Jika dites di LOKAL (php artisan serve), jalankan entry point standar Laravel
    require __DIR__ . '/../public/index.php';
}