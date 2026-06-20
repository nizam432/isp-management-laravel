<?php
// ════════════════════════════════════════════════════════════════
// Laravel 11+ হলে: bootstrap/app.php এ ->withMiddleware() এর ভেতরে যোগ করুন
// Laravel 10 বা নিচে হলে: app/Http/Kernel.php এর $middlewareAliases এ যোগ করুন
// ════════════════════════════════════════════════════════════════

// ── Laravel 11+ (bootstrap/app.php) ──────────────────────
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'reseller.active' => \App\Http\Middleware\EnsureResellerIsActive::class,
        'reseller.menu'   => \App\Http\Middleware\CheckResellerMenuAccess::class,
    ]);
})

// ── Laravel 10 বা নিচে (app/Http/Kernel.php) ─────────────
protected $middlewareAliases = [
    // ... existing aliases
    'reseller.active' => \App\Http\Middleware\EnsureResellerIsActive::class,
    'reseller.menu'   => \App\Http\Middleware\CheckResellerMenuAccess::class,
];
