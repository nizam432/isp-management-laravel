<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacReseller;
use App\Models\ResellerEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    /**
     * The mac_reseller guard authenticates two model types (MacReseller owner and ResellerEmployee staff).
     * Because a single guard provider expects one model, we manually check credentials against each table.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Try owner account first.
        $reseller = MacReseller::where('username', $credentials['username'])->first();

        if ($reseller && Hash::check($credentials['password'], $reseller->password)) {
            if (!$reseller->isLoginAllowed()) {
                $msg = $reseller->is_locked
                    ? 'Your account has been locked. Please contact admin.'
                    : 'Your account has been disabled. Please contact admin.';
                throw ValidationException::withMessages(['username' => $msg]);
            }

            Auth::guard('mac_reseller')->login($reseller, $request->boolean('remember'));
            $request->session()->regenerate();
            $request->session()->put('reseller_actor_type', 'owner');

            return redirect()->intended(route('reseller.dashboard'));
        }

        // Fall back to employee (staff) account.
        $employee = ResellerEmployee::where('username', $credentials['username'])->first();

        if ($employee && Hash::check($credentials['password'], $employee->password)) {
            if (!$employee->isLoginAllowed()) {
                throw ValidationException::withMessages([
                    'username' => 'Your account is inactive or the parent reseller account is disabled.',
                ]);
            }

            // Log in as the owner via the guard but store the employee identity in session for permission checks.
            Auth::guard('mac_reseller')->login($employee->macReseller, $request->boolean('remember'));
            $request->session()->regenerate();
            $request->session()->put('reseller_actor_type', 'employee');
            $request->session()->put('reseller_employee_id', $employee->id);

            return redirect()->intended(route('reseller.dashboard'));
        }

        throw ValidationException::withMessages([
            'username' => 'Invalid username or password.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('mac_reseller')->logout();
        $request->session()->forget(['reseller_actor_type', 'reseller_employee_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('reseller.login');
    }
}
