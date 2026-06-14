<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAuthenticate
{
    /**
     * Client portal routes protect করে।
     * Login না থাকলে /client/login এ redirect করে।
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('customer')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('client.login')
                ->with('error', 'এই পেজ দেখতে আগে লগইন করুন।');
        }

        return $next($request);
    }
}
