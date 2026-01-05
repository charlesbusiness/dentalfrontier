<?php

use App\Exception\ExceptionHandler;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RateLimiter;
use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'rateLimiter' => RateLimiter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
         $exceptions->render(function (Throwable $exception, $request) {

            if ($request->expectsJson()) {
                return (new ExceptionHandler)->handleApiException($exception);
            }

            return (new ExceptionHandler)->defaultExceptionHandler($exception);
        });
    })->create();

