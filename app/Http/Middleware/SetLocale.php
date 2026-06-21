<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Supported locales — নতুন ভাষা যোগ করতে চাইলে শুধু এখানে
     * একটা entry যোগ করুন এবং lang/{code}/ ফোল্ডার বানান।
     */
    public const SUPPORTED_LOCALES = [
        'en' => ['label' => 'English', 'native' => 'English', 'flag' => '🇬🇧', 'rtl' => false],
        'bn' => ['label' => 'Bangla',  'native' => 'বাংলা',   'flag' => '🇧🇩', 'rtl' => false],
        'ar' => ['label' => 'Arabic',  'native' => 'العربية', 'flag' => '🇸🇦', 'rtl' => true],
    ];

    public function handle(Request $request, Closure $next)
    {
        $locale = session('app_locale', config('app.locale', 'en'));

        if (!array_key_exists($locale, self::SUPPORTED_LOCALES)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
