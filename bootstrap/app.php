<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

/*         $middleware->prependToGroup('web', [
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        ]);
 */
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'superadmin'         => \App\Http\Middleware\SuperAdminMiddleware::class,
            'reseller'           => \App\Http\Middleware\ResellerMiddleware::class,
            'client.auth'        => \App\Http\Middleware\ClientAuthenticate::class,
            'reseller.active'    => \App\Http\Middleware\EnsureResellerIsActive::class,
            'reseller.menu'      => \App\Http\Middleware\CheckResellerMenuAccess::class,
        ]);

        // Exempt payment gateway callbacks from CSRF
        $middleware->validateCsrfTokens(except: [
            'client/payment/*/success',
            'client/payment/*/fail',
            'client/payment/*/cancel',
            'client/payment/*/ipn',
            'client/payment/*/callback',
            'client/payment/stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (TokenMismatchException $e, $request) {

            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.');

        });

    })
    ->create();