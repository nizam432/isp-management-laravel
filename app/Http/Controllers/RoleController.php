<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    private array $systemRoles = ['super-admin', 'isp-admin'];

    /**
     * Role list
     */
    public function index()
    {
        $roles = Role::whereNotIn('name', $this->systemRoles)
            ->withCount('permissions', 'users')
            ->latest()
            ->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Create form — Super Admin-এর সব permissions grouped করে দেখাবে
     */
    public function create()
    {
        $permissions = Permission::whereNotIn('name', ['super-admin', 'isp-admin', 'create-reseller'])
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store new role with selected permissions
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $roleName = strtolower(str_replace(' ', '-', trim($request->name)));

        $role = Role::create([
            'name'       => $roleName,
            'guard_name' => 'web',
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', "Role '{$roleName}' তৈরি হয়েছে।");
    }

    /**
     * Edit form
     */
    public function edit(Role $role)
    {
        if (in_array($role->name, $this->systemRoles)) {
            abort(403, 'System role পরিবর্তন করা যাবে না।');
        }

        $permissions        = Permission::whereNotIn('name', ['super-admin', 'isp-admin', 'create-reseller'])
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $assignedPermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'assignedPermissions'));
    }

    /**
     * Update role permissions
     */
    public function update(Request $request, Role $role)
    {
        if (in_array($role->name, $this->systemRoles)) {
            abort(403, 'System role পরিবর্তন করা যাবে না।');
        }

        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' আপডেট হয়েছে।");
    }

    /**
     * Delete role
     */
    public function destroy(Role $role)
    {
        if (in_array($role->name, $this->systemRoles)) {
            abort(403, 'System role delete করা যাবে না।');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "এই role-এ {$role->users()->count()} জন user আছে। আগে users সরান।");
        }

        $role->delete();

        return back()->with('success', "Role '{$role->name}' delete হয়েছে।");
    }
}
