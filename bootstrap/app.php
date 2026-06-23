<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            Log::warning('Falha de CSRF detectada.', [
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'has_session_cookie' => $request->cookies->has(config('session.cookie')),
                'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
                'has_x_csrf_token_header' => $request->headers->has('X-CSRF-TOKEN'),
                'has_x_xsrf_token_header' => $request->headers->has('X-XSRF-TOKEN'),
                'origin' => $request->headers->get('origin'),
                'referer' => $request->headers->get('referer'),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson() || $request->header('X-Inertia') || $request->is('admin/*')) {
                return response()->json([
                    'message' => 'Falha de autenticacao da sessao. Atualize a pagina e tente novamente.',
                    'error' => 'csrf_token_mismatch',
                ], 419);
            }

            return null;
        });
    })->create();
