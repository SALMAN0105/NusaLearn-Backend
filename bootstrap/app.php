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
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'administrator' => \App\Http\Middleware\AdministratorMiddleware::class,
            'guru' => \App\Http\Middleware\GuruMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tangani 404 (Not Found)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Endpoint tidak ditemukan.'
                ], 404);
            }
            
            // Redirect ke halaman sebelumnya, fallback ke login
            $fallbackUrl = '/admin/login';
            $previousUrl = $request->header('referer', $fallbackUrl);
            return redirect($previousUrl)->with('error', 'Halaman tidak ditemukan.');
        });

        // Tangani Session Expired (Token Mismatch / 419)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('login')->with('error', 'Sesi Anda telah kedaluwarsa. Silakan login kembali.');
        });

        // Tangani Unauthenticated (Sesi habis / Belum Login)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Sesi Anda telah kedaluwarsa. Silakan login kembali.');
        });
    })->create();
