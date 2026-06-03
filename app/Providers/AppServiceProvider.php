<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(\App\Models\Setting::class, 'Setting');
    }

    public function boot(): void
    {
        //
    }
}