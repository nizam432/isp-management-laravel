<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Get group name from permission name
     */
    private function getGroup(string $name): string
    {
        $parts = explode('.', $name);
        return ucfirst($parts[0]);
    }

    /**
     * Show isp-admin role permission management page
     */
    public function ispAdminPermissions()
    {
        $role = Role::firstOrCreate(['name' => 'isp-admin', 'guard_name' => 'web']);

        $permissions = Permission::whereNotIn('name', ['super-admin', 'create-reseller'])
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => $this->getGroup($p->name))
            ->sortKeys();

        $assignedPermissions = $role->permissions->pluck('name')->toArray();

        return view('super-admin.roles.isp-admin', compact('permissions', 'assignedPermissions', 'role'));
    }

    /**
     * Update isp-admin role permissions
     */
    public function updateIspAdminPermissions(Request $request)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::firstOrCreate(['name' => 'isp-admin', 'guard_name' => 'web']);
        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', 'isp-admin role permissions updated successfully. All ISP tenants will reflect this change.');
    }
}
