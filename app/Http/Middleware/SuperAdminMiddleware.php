<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware 
{
    /**
     * Previously this checked the default 'web' guard (auth()->check(),
     * auth()->user()->hasRole('super-admin')) — which resolves against
     * App\Models\User, a TENANT-side model living in each tenant's own
     * database. The central database has no `users` table at all (only
     * `super_admins`), so this middleware could never actually work when
     * accessed from the central domain — it would only "succeed" if somehow
     * running inside a tenant's own database context, which defeats the
     * purpose of a cross-tenant Super Admin.
     *
     * Fixed to check the dedicated 'super_admin' guard instead, which
     * correctly resolves against App\Models\SuperAdmin in the central DB.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth('super_admin')->check()) {
            return redirect()->route('super-admin.login');
        }

        if (!auth('super_admin')->user()->is_active) {
            auth('super_admin')->logout();
            abort(403, 'Account is inactive.');
        }

        return $next($request);
    }
}