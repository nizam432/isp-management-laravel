<?php

namespace App\Http\Controllers\HR;

use App\Models\HR\Employee;
use App\Models\HR\Department;
use App\Models\HR\Position;
use App\Models\HR\EmployeeDocument;
use App\Models\HR\EmployeeEducation;
use App\Models\HR\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with(['department', 'position'])
            ->when($request->search, fn($q) => $q
                ->where('name',          'like', "%{$request->search}%")
                ->orWhere('phone',        'like', "%{$request->search}%")
                ->orWhere('employee_code','like', "%{$request->search}%"))
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->status,        fn($q) => $q->where('status',        $request->status))
            ->latest()
            ->paginate(20);

        $departments = Department::active()->get();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::active()->get();
        $positions   = Position::active()->get();
        return view('employees.create', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:100|unique:users,email',
            'department_id' => 'nullable|exists:departments,id',
            'position_id'   => 'nullable|exists:positions,id',
            'join_date'     => 'nullable|date',
            'basic_salary'  => 'nullable|numeric|min:0',
            'password'      => 'required|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            // Create user account
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Photo upload
            $photo = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo')->store('employees/photos', 'public');
            }

            // Create employee
            $employee = Employee::create([
                'employee_code' => Employee::generateCode(),
                'user_id'       => $user->id,
                'department_id' => $request->department_id,
                'position_id'   => $request->position_id,
                'name'          => $request->name,
                'phone'         => $request->phone,
                'email'         => $request->email,
                'nid_number'    => $request->nid_number,
                'photo'         => $photo,
                'join_date'     => $request->join_date,
                'status'        => 'active',
                'present_address'   => $request->present_address,
                'permanent_address' => $request->permanent_address,
                'basic_salary'  => $request->basic_salary ?? 0,
                'salary_date'   => $request->salary_date ?? 1,
                'emergency_name'     => $request->emergency_name,
                'emergency_phone'    => $request->emergency_phone,
                'emergency_relation' => $request->emergency_relation,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'branch_name'    => $request->branch_name,
                'created_by'     => auth()->id(),
            ]);

            // Documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $file) {
                    $path = $file->store('employees/documents', 'public');
                    EmployeeDocument::create([
                        'employee_id'   => $employee->id,
                        'document_name' => $request->document_names[$index] ?? $file->getClientOriginalName(),
                        'file_path'     => $path,
                    ]);
                }
            }

            // Educations
            if ($request->has('educations')) {
                foreach ($request->educations as $edu) {
                    if (!empty($edu['degree'])) {
                        EmployeeEducation::create([
                            'employee_id'  => $employee->id,
                            'degree'       => $edu['degree'],
                            'institution'  => $edu['institution'] ?? null,
                            'passing_year' => $edu['passing_year'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('employees.show', $employee)
                             ->with('success', "Employee '{$employee->name}' created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Employee create failed: " . $e->getMessage());
            return back()->with('error', 'Failed to create employee: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'position', 'documents', 'educations', 'payrolls', 'advances', 'leaves.leaveType']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load(['documents', 'educations']);
        $departments = Department::active()->get();
        $positions   = Position::active()->get();
        return view('employees.edit', compact('employee', 'departments', 'positions'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'position_id'   => 'nullable|exists:positions,id',
            'basic_salary'  => 'nullable|numeric|min:0',
            'status'        => 'required|in:active,inactive,resigned,terminated',
        ]);

        $photo = $employee->photo;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee->update([
            'department_id'  => $request->department_id,
            'position_id'    => $request->position_id,
            'name'           => $request->name,
            'phone'          => $request->phone,
            'nid_number'     => $request->nid_number,
            'photo'          => $photo,
            'join_date'      => $request->join_date,
            'status'         => $request->status,
            'leaving_date'   => $request->leaving_date,
            'leaving_reason' => $request->leaving_reason,
            'leaving_note'   => $request->leaving_note,
            'present_address'   => $request->present_address,
            'permanent_address' => $request->permanent_address,
            'basic_salary'  => $request->basic_salary ?? 0,
            'salary_date'   => $request->salary_date ?? 1,
            'emergency_name'     => $request->emergency_name,
            'emergency_phone'    => $request->emergency_phone,
            'emergency_relation' => $request->emergency_relation,
            'bank_name'      => $request->bank_name,
            'account_number' => $request->account_number,
            'branch_name'    => $request->branch_name,
        ]);

        // Update user status
        if ($employee->user) {
            $employee->user->update([
                'name' => $request->name,
            ]);
            // Block login if not active
            if (!in_array($request->status, ['active'])) {
                // You can add a 'is_active' column to users table or use a different approach
            }
        }

        // New documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $index => $file) {
                $path = $file->store('employees/documents', 'public');
                EmployeeDocument::create([
                    'employee_id'   => $employee->id,
                    'document_name' => $request->document_names[$index] ?? $file->getClientOriginalName(),
                    'file_path'     => $path,
                ]);
            }
        }

        // Update educations
        if ($request->has('educations')) {
            $employee->educations()->delete();
            foreach ($request->educations as $edu) {
                if (!empty($edu['degree'])) {
                    EmployeeEducation::create([
                        'employee_id'  => $employee->id,
                        'degree'       => $edu['degree'],
                        'institution'  => $edu['institution'] ?? null,
                        'passing_year' => $edu['passing_year'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('employees.show', $employee)
                         ->with('success', "Employee '{$employee->name}' updated successfully.");
    }

    public function destroy(Employee $employee)
    {
        $employee->documents()->each(function($doc) {
            \Storage::disk('public')->delete($doc->file_path);
            $doc->delete();
        });
        if ($employee->user) $employee->user->delete();
        $employee->delete();

        return redirect()->route('employees.index')
                         ->with('success', 'Employee deleted successfully.');
    }

    public function destroyDocument(EmployeeDocument $document)
    {
        \Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }

    public function getPositions(Department $department)
    {
        return response()->json($department->positions()->active()->get(['id', 'name']));
    }
}
