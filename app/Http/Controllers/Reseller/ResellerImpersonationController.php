<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ResellerImpersonationController extends Controller
{
    /**
     * "Back to Admin" — ends the reseller-portal session and returns the ISP
     * Admin to their own panel. The Admin's own guard session is usually
     * still intact underneath (Laravel supports multiple guards active in
     * the same session at once), but loginUsingId() is used as a fallback
     * in case it was cleared for any reason.
     */
    public function backToAdmin()
    {
        $adminId    = session('impersonator_admin_id');
        $adminGuard = session('impersonator_admin_guard', 'web');

        if (!$adminId) {
            // not an impersonated session — nothing to return to
            return redirect()->route('reseller.dashboard');
        }

        Auth::guard('mac_reseller')->logout();
        session()->forget(['impersonator_admin_id', 'impersonator_admin_guard']);

        if (!Auth::guard($adminGuard)->check()) {
            Auth::guard($adminGuard)->loginUsingId($adminId);
        }

        return redirect()->route('mac-reseller.list.index')
            ->with('success', 'Returned to Admin panel.');
    }
}