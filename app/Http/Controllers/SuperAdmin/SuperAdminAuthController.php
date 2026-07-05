<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /** GET /super-admin/login — show the login form. */
    public function showLoginForm()
    {
        if (Auth::guard('super_admin')->check()) {
            return redirect()->route('super-admin.dashboard');
        }

        return view('super-admin.auth.login');
    }

    /** POST /super-admin/login */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Only active super admins can log in.
        if (Auth::guard('super_admin')->attempt(
            array_merge($credentials, ['is_active' => true]),
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            return redirect()->intended(route('super-admin.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়, অথবা অ্যাকাউন্ট নিষ্ক্রিয়।'])
            ->onlyInput('email');
    }

    /** POST /super-admin/logout */
    public function logout(Request $request)
    {
        Auth::guard('super_admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login');
    }
}
