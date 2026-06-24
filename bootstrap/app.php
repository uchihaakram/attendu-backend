<?php

use App\Http\Middleware\CheckAiApiKey;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\JsonUnicodeResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: [__DIR__ . '/../routes/api.php', __DIR__ . '/../routes/auth/api.php'],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'json.unicode' => JsonUnicodeResponse::class,
            'ai.key'       => CheckAiApiKey::class, // ← أضف السطر ده

        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
