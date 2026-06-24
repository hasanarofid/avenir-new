<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'admin/emitens/generate-ai',
            '/admin/emitens/generate-ai',
        ]);

        $middleware->alias([
            'premium' => \App\Http\Middleware\EnsureUserIsPremium::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('admin/emitens/generate-ai'),
        );
        // Handle PostTooLargeException globally
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, Request $request) {
            return redirect()->back()->withErrors([
                'portfolio_pdf' => 'Ukuran file atau data yang dikirim terlalu besar. Maksimal 2MB.',
                'error' => 'Ukuran file atau form data terlalu besar.'
            ]);
        });

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if ($response->getStatusCode() === 503) {
                return \Inertia\Inertia::render('Error', ['status' => 503])
                    ->toResponse($request)
                    ->setStatusCode(503);
            }
            
            // For other HTTP errors, only render in production/staging to allow local debug stacktraces
            if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [500, 404, 403])) {
                return \Inertia\Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }


            // Always render 404 and 403 locally too, because they are user-facing errors
            if (app()->environment(['local']) && in_array($response->getStatusCode(), [404, 403])) {
                return \Inertia\Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
