<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ResellerEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResellerEmployeeController extends Controller
{
    const MENUS = [
        'CONFIGURATION', 'MIKROTIK CLIENT', 'CLIENT', 'BILLING',
        'MONITORING', 'CLIENT SUPPORT', 'SMS SERVICE', 'REPORT',
        'FUND HISTORY', 'TUTORIALS',
    ];

    /**
     * শুধু Owner (reseller নিজে) এই controller access করতে পারবে —
     * Employee নিজে অন্য employee তৈরি/edit করতে পারবে না।
     */
    private function ensureOwner(Request $request)
    {
        abort_if($request->session()->get('reseller_employee_id'), 403, 'Only the reseller owner can manage employees.');
    }

    public function index(Request $request)
    {
        $this->ensureOwner($request);

        $resellerId = Auth::guard('mac_reseller')->id();
        $employees  = ResellerEmployee::where('mac_reseller_id', $resellerId)->latest()->paginate(25);

        return view('reseller.employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $this->ensureOwner($request);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|string|max:20',
            'designation'  => 'nullable|string|max:100',
            'username'     => 'required|string|unique:reseller_employees,username',
            'password'     => 'required|string|min:6|confirmed',
            'allowed_menus'=> 'nullable|array',
        ]);

        $data['mac_reseller_id'] = Auth::guard('mac_reseller')->id();
        $data['password']        = Hash::make($data['password']);
        $data['allowed_menus']   = $request->input('allowed_menus', []);

        ResellerEmployee::create($data);

        return response()->json(['success' => true, 'message' => 'Employee added successfully.']);
    }

    public function edit(Request $request, ResellerEmployee $employee)
    {
        $this->ensureOwner($request);
        $this->authorizeOwnership($employee);

        return response()->json($employee);
    }

    public function update(Request $request, ResellerEmployee $employee)
    {
        $this->ensureOwner($request);
        $this->authorizeOwnership($employee);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|string|max:20',
            'designation'  => 'nullable|string|max:100',
            'username'     => 'required|string|unique:reseller_employees,username,' . $employee->id,
            'password'     => 'nullable|string|min:6|confirmed',
            'allowed_menus'=> 'nullable|array',
            'is_active'    => 'nullable|boolean',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['allowed_menus'] = $request->input('allowed_menus', []);
        $data['is_active']     = $request->boolean('is_active', true);

        $employee->update($data);

        return response()->json(['success' => true, 'message' => 'Employee updated successfully.']);
    }

    public function destroy(Request $request, ResellerEmployee $employee)
    {
        $this->ensureOwner($request);
        $this->authorizeOwnership($employee);

        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee removed.']);
    }

    public function toggle(Request $request, ResellerEmployee $employee)
    {
        $this->ensureOwner($request);
        $this->authorizeOwnership($employee);

        $employee->update(['is_active' => !$employee->is_active]);

        return response()->json(['success' => true]);
    }

    private function authorizeOwnership(ResellerEmployee $employee)
    {
        abort_unless($employee->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}
