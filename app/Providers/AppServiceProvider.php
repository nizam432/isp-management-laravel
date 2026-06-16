<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

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
    }

    public function boot(): void
    {
        //
    }
}
