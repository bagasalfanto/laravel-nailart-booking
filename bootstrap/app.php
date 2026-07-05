<?php

use App\Http\Middleware\EnsureProfileIsComplete;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'profile.complete' => EnsureProfileIsComplete::class,
        ]);

        // Paksa user dengan must_complete_profile=true ke halaman setup pada semua request web yg authenticated.
        $middleware->web(append: [
            EnsureProfileIsComplete::class,
        ]);

        $middleware->trustProxies(at: '*');

        // Webhook Midtrans datang tanpa CSRF token — exempt route ini biar gak 419.
        $middleware->validateCsrfTokens(except: [
            'payment/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
