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
        $positions   = Position::with('department')->withCount('employees')->latest()->get();
        $departments = Department::active()->get();
        return view('hr.positions.index', compact('positions', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);
        Position::create($request->only('name', 'department_id', 'description') + ['is_active' => true]);
        return back()->with('success', "Position '{$request->name}' created.");
    }

    public function update(Request $request, Position $position)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $position->update($request->only('name', 'department_id', 'description'));
        return back()->with('success', 'Position updated.');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete — employees assigned to this position.');
        }
        $position->delete();
        return back()->with('success', 'Position deleted.');
    }
}
