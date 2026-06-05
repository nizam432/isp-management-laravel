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
    }

    public function boot(): void
    {
        //
    }
}