<?php

namespace App\Http\Middleware;

use App\Models\ResellerEmployee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckResellerMenuAccess
{
    /**
     * Route এ menu key পাস করো parameter হিসেবে, যেমন:
     * Route::middleware('reseller.menu:CLIENT')->group(...)
     *
     * Owner login করলে — তার নিজের allowed_menus চেক হয় (admin যা দিয়েছে)।
     * Employee login করলে — session এ employee_id থাকে, সেই employee এর
     * নিজস্ব allowed_menus চেক হয় (যেটা owner নিজে সেট করেছে employee-কে)।
     */
    public function handle(Request $request, Closure $next, string $menuKey)
    {
        $reseller   = Auth::guard('mac_reseller')->user();
        $employeeId = $request->session()->get('reseller_employee_id');

        if (!$reseller) {
            abort(403, 'Unauthorized.');
        }

        if ($employeeId) {
            // Employee হিসেবে login — employee এর নিজের permission চেক
            $employee = ResellerEmployee::find($employeeId);
            if (!$employee || !$employee->canAccessMenu($menuKey)) {
                abort(403, 'You do not have access to this section. Please contact your reseller admin.');
            }
        } else {
            // Owner হিসেবে login — reseller এর নিজের permission চেক
            if (!$reseller->canAccessMenu($menuKey)) {
                abort(403, 'You do not have access to this section. Please contact admin.');
            }
        }

        return $next($request);
    }
}
