<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // RFC 8058 one-click unsubscribe (Gmail / Apple Mail) POSTs directly to
        // the tracking endpoint without a CSRF token. The URL is signed and
        // token-protected, so skipping CSRF here is safe.
        $middleware->validateCsrfTokens(except: [
            't/u/*',
        ]);

        // Custom API token authentication — see app/Http/Middleware/AuthenticateApiToken.
        // Used on every /api/v1/* route that needs a logged-in user.
        $middleware->alias([
            'api.token' => \App\Http\Middleware\AuthenticateApiToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
