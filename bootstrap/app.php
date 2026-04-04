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
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('admin/*') && !config('app.debug')) {
                return response()->view('errors.500', [], 500);
            }
            
            if ($request->is('api/*') || $request->wantsJson()) {
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
                }
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
                }
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException || $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json(['success' => false, 'message' => 'Resource not found.'], 404);
                }
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Internal Server Error.',
                    'data'    => null,
                    'meta'    => null,
                    'errors'  => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }
        });
    })->create();
