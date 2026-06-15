<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    private array $systemPermissions = ['super-admin', 'isp-admin', 'create-reseller'];

    private function getGroup(string $name): string
    {
        return str_contains($name, '.') ? ucfirst(explode('.', $name)[0]) : ucfirst($name);
    }

    public function index()
    {
        $permissions = Permission::whereNotIn('name', $this->systemPermissions)
            ->withCount('roles')
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => $this->getGroup($p->name))
            ->sortKeys();

        $totalCount = Permission::whereNotIn('name', $this->systemPermissions)->count();
        $groupList  = $permissions->keys();

        return view('super-admin.permissions.index', compact('permissions', 'totalCount', 'groupList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'group' => 'required|string|max:100',
        ]);

        $group   = strtolower(trim($request->group));
        $actions = array_filter(array_map('trim', explode(',', $request->name)));

        $created = [];
        $skipped = [];

        foreach ($actions as $action) {
            $action   = strtolower(str_replace([' ', '-'], '.', $action));
            $permName = str_starts_with($action, $group . '.') ? $action : $group . '.' . $action;

            if (Permission::where('name', $permName)->exists()) {
                $skipped[] = $permName;
                continue;
            }

            Permission::create(['name' => $permName, 'guard_name' => 'web']);
            $created[] = $permName;
        }

        // Auto assign all created permissions to isp-admin role
        if ($created) {
            $ispAdminRole = Role::findByName('isp-admin');
            if ($ispAdminRole) {
                $ispAdminRole->givePermissionTo($created);
            }
        }

        $msg = '';
        if ($created) $msg .= 'Created: ' . implode(', ', $created) . '. ';
        if ($skipped) $msg .= 'Already exists: ' . implode(', ', $skipped) . '.';

        return back()->with($created ? 'success' : 'error', trim($msg));
    }

    public function update(Request $request, Permission $permission)
    {
        if (in_array($permission->name, $this->systemPermissions)) {
            return back()->with('error', 'System permission cannot be edited.');
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name,' . $permission->id,
        ]);

        $newName = strtolower(str_replace([' ', '-'], '.', trim($request->name)));

        $old = $permission->name;
        $permission->update(['name' => $newName]);

        return back()->with('success', "Permission renamed: '{$old}' → '{$newName}'.");
    }

    public function destroy(Permission $permission)
    {
        if (in_array($permission->name, $this->systemPermissions)) {
            return back()->with('error', 'System permission cannot be deleted.');
        }

        $name = $permission->name;
        $permission->delete();

        return back()->with('success', "Permission '{$name}' deleted.");
    }
}
