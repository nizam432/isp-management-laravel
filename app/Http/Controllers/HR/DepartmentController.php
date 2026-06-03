<?php

namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller; 
use App\Models\HR\Department;
use App\Models\HR\Position;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['positions', 'employees'])->latest()->get();
        $positions   = Position::with('department')->latest()->get();
        return view('hr.departments.index', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:departments,name']);
        Department::create($request->only('name', 'description') + ['is_active' => true]);
        return back()->with('success', "Department '{$request->name}' created.");
    }

    public function update(Request $request, Department $department)
    {
        $request->validate(['name' => 'required|string|max:100|unique:departments,name,' . $department->id]);
        $department->update($request->only('name', 'description'));
        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete — employees assigned to this department.');
        }
        $department->positions()->delete();
        $department->delete();
        return back()->with('success', 'Department deleted.');
    }
}

