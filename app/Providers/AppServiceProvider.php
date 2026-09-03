<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use App\Models\ClientSupportTicket;
use App\Observers\ClientSupportTicketObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Paginator::useBootstrap();
        $this->app->alias(\App\Models\Setting::class, 'Setting');

        // Force QR code to use GD backend — no Imagick needed
        $this->app->bind(
            \BaconQrCode\Renderer\Image\ImagickImageBackEnd::class,
            \BaconQrCode\Renderer\Image\GDLibImageBackEnd::class
        );

        // Point the navbar "Profile" link to our actual /profile page.
        // Must be in register() (not boot()) — ALL providers' register() methods
        // run before ANY provider's boot() runs, and AdminLteServiceProvider reads
        // this config during ITS boot() to build the navbar menu. Setting it in
        // our own boot() was too late (AdminLTE's menu was already built by then),
        // which is why the navbar link kept showing the old 'dashboard' href.
        config(['adminlte.usermenu_profile_url' => 'profile']);
    }

    public function boot(): void
    {
        ClientSupportTicket::observe(ClientSupportTicketObserver::class);

        // AdminLTE's sidebar menu items use 'can' => 'super-admin' to show/hide
        // the Super Admin section — but that check runs against the DEFAULT
        // ('web') guard's user by default (Auth::user()->can(...)). Since Super
        // Admin logs in via a separate 'super_admin' guard (App\Models\SuperAdmin,
        // no relation to App\Models\User), the default guard has no user at all
        // when browsing the super-admin panel — so every menu item with
        // 'can' => 'super-admin' was being hidden. This Gate checks the correct
        // guard directly, ignoring the default-guard $user Laravel normally passes.
        Gate::define('super-admin', function ($user = null) {
            return Auth::guard('super_admin')->check();
        });

        // The 'isp-admin' role is meant to be each tenant's own top-level
        // admin — reasonably expected to have full access within their own
        // tenant by default. Individual permission syncing (central →
        // tenant) turned out to be a dead end: the central database has no
        // `permissions` table at all (Super Admin's "Permissions" /
        // "ISP Admin Permissions" pages were never actually functional —
        // only ever seen in the sidebar, never exercised). This bypass
        // grants every `can:` check automatically for isp-admin, avoiding
        // reliance on a permission catalog that doesn't exist yet.
        Gate::before(function ($user, $ability) {
            // Exclude 'super-admin' — otherwise a normal ISP tenant admin
            // (isp-admin role) would also see/pass the Super Admin sidebar
            // section, since this bypass would grant that ability too.
            if ($ability === 'super-admin') {
                return null;
            }
            if (method_exists($user, 'hasRole') && $user->hasRole('isp-admin')) {
                return true;
            }
            return null; // fall through to normal permission checks for everyone else
        });
    }
}
