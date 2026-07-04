<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
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
    }
}
