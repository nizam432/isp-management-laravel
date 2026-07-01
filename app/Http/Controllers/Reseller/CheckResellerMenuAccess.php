<?php

namespace App\Http\Middleware;

use App\Models\ResellerEmployee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckResellerMenuAccess
{
    /**
     * Usage: Route::middleware('reseller.menu:CLIENT')->group(...)
     * Owners are checked against their own allowed_menus (set by admin).
     * Employees are checked against their own allowed_menus (set by the owner).
     */
    public function handle(Request $request, Closure $next, string $menuKey)
    {
        $reseller   = Auth::guard('mac_reseller')->user();
        $employeeId = $request->session()->get('reseller_employee_id');

        if (!$reseller) {
            abort(403, 'Unauthorized.');
        }

        if ($employeeId) {
            $employee = ResellerEmployee::find($employeeId);
            if (!$employee || !$employee->canAccessMenu($menuKey)) {
                abort(403, 'You do not have access to this section. Please contact your reseller admin.');
            }
        } else {
            if (!$reseller->canAccessMenu($menuKey)) {
                abort(403, 'You do not have access to this section. Please contact admin.');
            }
        }

        return $next($request);
    }
}
