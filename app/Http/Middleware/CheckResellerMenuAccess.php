<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckResellerMenuAccess
{
    /**
     * Route এ menu key পাস করো parameter হিসেবে, যেমন:
     * Route::middleware('reseller.menu:CLIENT')->group(...)
     */
    public function handle(Request $request, Closure $next, string $menuKey)
    {
        $reseller = Auth::guard('mac_reseller')->user();

        if (!$reseller || !$reseller->canAccessMenu($menuKey)) {
            abort(403, 'You do not have access to this section. Please contact admin.');
        }

        return $next($request);
    }
}
