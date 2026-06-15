<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // ── Roles allowed for ISP Admin to assign ──
    private array $allowedRoles = ['manager', 'staff', 'agent', 'accountant', 'support'];

    /**
     * List all staff users (excludes super-admin & isp-admin itself)
     */
    public function index()
    {
        $users = User::with('roles')
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['super-admin', 'isp-admin']))
            ->latest()
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $roles = Role::whereIn('name', $this->allowedRoles)->get();
        return view('users.create', compact('roles'));
    }

    /**
     * Store new staff user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|string|in:' . implode(',', $this->allowedRoles),
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        ActivityLog::log('User created', 'User', $user->id, null, [
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $request->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' ({$request->role}) created successfully.");
    }

    /**
     * Show edit form
     */
    public function edit(User $user)
    {
        // Prevent editing super-admin / isp-admin via this controller
        if ($user->hasAnyRole(['super-admin', 'isp-admin'])) {
            abort(403, 'Cannot edit this user.');
        }

        $roles       = Role::whereIn('name', $this->allowedRoles)->get();
        $currentRole = $user->roles->first()?->name;

        return view('users.edit', compact('user', 'roles', 'currentRole'));
    }

    /**
     * Update user info & role
     */
    public function update(Request $request, User $user)
    {
        if ($user->hasAnyRole(['super-admin', 'isp-admin'])) {
            abort(403, 'Cannot edit this user.');
        }

        $request->validate([
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|string|in:' . implode(',', $this->allowedRoles),
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name'  => $request->name,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync role — remove old, assign new
        $user->syncRoles([$request->role]);

        ActivityLog::log('User updated', 'User', $user->id, null, [
            'name' => $user->name,
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Toggle active/inactive
     */
    public function toggle(User $user)
    {
        if ($user->hasAnyRole(['super-admin', 'isp-admin'])) {
            abort(403, 'Cannot modify this user.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        ActivityLog::log("User {$status}", 'User', $user->id);

        return back()->with('success', "User '{$user->name}' {$status}.");
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        if ($user->hasAnyRole(['super-admin', 'isp-admin'])) {
            abort(403, 'Cannot delete this user.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::log('User deleted', 'User', $user->id, ['name' => $name]);

        return back()->with('success', "User '{$name}' deleted.");
    }
}
