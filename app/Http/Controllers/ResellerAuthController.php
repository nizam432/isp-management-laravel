<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ResellerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('mac_reseller')->check()) {
            return redirect()->route('reseller.dashboard');
        }
        return view('reseller.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::guard('mac_reseller')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => 'Invalid username or password.',
            ]);
        }

        $reseller = Auth::guard('mac_reseller')->user();

        if (!$reseller->isLoginAllowed()) {
            Auth::guard('mac_reseller')->logout();
            $msg = $reseller->is_locked
                ? 'Your account has been locked. Please contact admin.'
                : 'Your account has been disabled. Please contact admin.';
            throw ValidationException::withMessages(['username' => $msg]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('reseller.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('mac_reseller')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('reseller.login');
    }
}
