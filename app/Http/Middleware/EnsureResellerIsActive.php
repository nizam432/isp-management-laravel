<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureResellerIsActive
{
    /**
     * Reseller-এর account active না থাকলে অথবা locked থাকলে logout করে দাও।
     */
    public function handle(Request $request, Closure $next)
    {
        $reseller = Auth::guard('mac_reseller')->user();

        if ($reseller && (!$reseller->is_active || $reseller->is_locked)) {
            Auth::guard('mac_reseller')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $reseller->is_locked
                ? 'Your account has been locked. Please contact admin.'
                : 'Your account has been disabled. Please contact admin.';

            return redirect()->route('reseller.login')->with('error', $message);
        }

        return $next($request);
    }
}
