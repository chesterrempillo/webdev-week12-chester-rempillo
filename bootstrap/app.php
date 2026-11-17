<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Prevent API guests from being redirected to a login page
        $middleware->redirectGuestsTo(
            fn (Request $request) =>
                $request->is('api/*') ? null : '/login'
        );

        // Register Laravel Sanctum middleware aliases
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') || $request->expectsJson(),
        );

        // Validation error: 422
        $exceptions->render(function (
            ValidationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Unauthenticated: 401
        $exceptions->render(function (
            AuthenticationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        // Not found: 404
        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }
        });

        // Forbidden: 403
        $exceptions->render(function (
            AuthorizationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.',
                ], 403);
            }
        });

        $exceptions->render(function (
            HttpException $e,
            Request $request
        ) {
            if ($request->is('api/*') && $e->getStatusCode() === 403) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.',
                ], 403);
            }
        });

    })->create();
