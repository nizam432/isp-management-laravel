<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    private array $systemRoles = ['super-admin', 'isp-admin'];

    /**
     * Get group name from permission name
     * e.g. accounting.expense.approve -> Accounting
     * e.g. customer.view -> Customer
     */
    private function getGroup(string $name): string
    {
        $parts = explode('.', $name);
        return ucfirst($parts[0]);
    }

    public function index()
    {
        $roles = Role::whereNotIn('name', $this->systemRoles)
            ->withCount('permissions', 'users')
            ->latest()
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::whereNotIn('name', ['super-admin', 'isp-admin', 'create-reseller'])
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => $this->getGroup($p->name))
            ->sortKeys();

        return view('roles.create', compact('permissions'));
    }

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

        // Only allow permissions that isp-admin has
        $allowedPermissions = auth()->user()->getAllPermissions()->pluck('name')->toArray();

        $selectedPermissions = collect($request->permissions ?? [])
            ->filter(fn($p) => in_array($p, $allowedPermissions))
            ->toArray();

        $role->syncPermissions($selectedPermissions);

        return redirect()->route('roles.index')
            ->with('success', "Role '{$roleName}' created successfully.");
    }

    public function edit(Role $role)
    {
        if (in_array($role->name, $this->systemRoles)) {
            abort(403, 'System role cannot be modified.');
        }

        $permissions = Permission::whereNotIn('name', ['super-admin', 'isp-admin', 'create-reseller'])
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => $this->getGroup($p->name))
            ->sortKeys();

        $assignedPermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'assignedPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if (in_array($role->name, $this->systemRoles)) {
            abort(403, 'System role cannot be modified.');
        }

        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Only allow permissions that isp-admin has
        $allowedPermissions = auth()->user()->getAllPermissions()->pluck('name')->toArray();

        $selectedPermissions = collect($request->permissions ?? [])
            ->filter(fn($p) => in_array($p, $allowedPermissions))
            ->toArray();

        $role->syncPermissions($selectedPermissions);

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, $this->systemRoles)) {
            abort(403, 'System role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "This role has {$role->users()->count()} user(s). Remove users first.");
        }

        $name = $role->name;
        $role->delete();

        return back()->with('success', "Role '{$name}' deleted.");
    }
}
