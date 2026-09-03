<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Position;
use App\Models\HR\Department;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('department')
            ->withCount('employees')
            ->whereHas('department', function ($q) {
                $q->whereNull('mac_reseller_id');   // shudu isp-admin er department er position
            })
            ->latest()
            ->get();

        $departments = Department::active()
            ->whereNull('mac_reseller_id')          // dropdown e o shudu isp-admin er department
            ->get();

        return view('hr.positions.index', compact('positions', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        $department = Department::findOrFail($request->department_id);

        // isp-admin sudu tar nijer department er jonno position create korte parbe
        if (!is_null($department->mac_reseller_id)) {
            abort(403, 'Unauthorized department selected.');
        }

        Position::create($request->only('name', 'department_id', 'description') + ['is_active' => true]);

        return back()->with('success', "Position '{$request->name}' created.");
    }

    public function update(Request $request, Position $position)
    {
        // existing position tar department check
        if (!is_null($position->department?->mac_reseller_id)) {
            abort(403, 'Unauthorized access to this position.');
        }

        $request->validate(['name' => 'required|string|max:100']);

        // jodi department_id change kora hoy, notun department o isp-admin er kina check koro
        if ($request->filled('department_id')) {
            $newDepartment = Department::findOrFail($request->department_id);
            if (!is_null($newDepartment->mac_reseller_id)) {
                abort(403, 'Unauthorized department selected.');
            }
        }

        $position->update($request->only('name', 'department_id', 'description'));

        return back()->with('success', 'Position updated.');
    }

    public function destroy(Position $position)
    {
        if (!is_null($position->department?->mac_reseller_id)) {
            abort(403, 'Unauthorized access to this position.');
        }

        if ($position->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete — employees assigned to this position.');
        }

        $position->delete();

        return back()->with('success', 'Position deleted.');
    }
}