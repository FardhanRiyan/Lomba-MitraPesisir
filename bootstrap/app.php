<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias middleware RBAC: ->middleware('role:ADMIN,NELAYAN')
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        // Tamu yang membuka halaman khusus anggota diarahkan ke halaman Masuk.
        $middleware->redirectGuestsTo(function (Request $request) {
            $request->session()->flash('warning', 'Silakan masuk terlebih dahulu untuk melanjutkan.');

            return route('auth');
        });

        // Pengguna yang sudah login tidak perlu melihat halaman Masuk/Daftar lagi.
        $middleware->redirectUsersTo(fn () => route('beranda'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Halaman error kustom ada di resources/views/errors (403, 404, 419, 429, 500, 503).
    })
    ->create();
