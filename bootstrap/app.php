<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
        // NOTE: La route stripe/webhook est gérée par Laravel\Cashier\Http\Controllers\WebhookController
        // qui vérifie automatiquement la signature via STRIPE_WEBHOOK_SECRET (config services.stripe.webhook_secret).
        // TODO SEC-C3: S'assurer que STRIPE_WEBHOOK_SECRET est défini dans .env et que cashier.webhook_secret est configuré.
        // Voir : https://laravel.com/docs/cashier-stripe#handling-stripe-webhooks

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            // En-têtes de sécurité HTTP par défaut sur toutes les réponses (cf. audit F-7).
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'subscribed' => \App\Http\Middleware\EnsureSubscribed::class,
            '2fa'        => \App\Http\Middleware\EnsureTwoFactorAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::render('Error', [
                    'status' => $e->getStatusCode(),
                    'message' => $e->getMessage() ?: \Symfony\Component\HttpFoundation\Response::$statusTexts[$e->getStatusCode()] ?? 'Erreur',
                ])->toResponse($request)->setStatusCode($e->getStatusCode());
            }
        });
        $exceptions->dontReport([
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Symfony\Component\HttpKernel\Exception\HttpException::class,
            \Illuminate\Validation\ValidationException::class,
        ]);
    })
    ->create();
