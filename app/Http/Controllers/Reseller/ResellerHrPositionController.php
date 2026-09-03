<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\HR\Department;
use App\Models\HR\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerHrPositionController extends Controller
{
    public function index()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $positions   = Position::forReseller($resellerId)->with('department')->orderBy('name')->get();
        $departments = Department::forReseller($resellerId)->active()->orderBy('name')->get(['id', 'name']);

        return view('reseller.hr.position.index', compact('positions', 'departments'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
        ]);

        Department::forReseller($resellerId)->findOrFail($data['department_id']);

        Position::create([
            'mac_reseller_id' => $resellerId,
            'department_id'   => $data['department_id'],
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Position added successfully.');
    }

    public function update(Request $request, Position $position)
    {
        $this->authorizeItem($position);
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'nullable|boolean',
        ]);

        Department::forReseller($resellerId)->findOrFail($data['department_id']);

        $position->update([
            'department_id' => $data['department_id'],
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'is_active'     => $request->boolean('is_active', $position->is_active),
        ]);

        return back()->with('success', 'Position updated successfully.');
    }

    public function toggle(Position $position)
    {
        $this->authorizeItem($position);
        $position->update(['is_active' => !$position->is_active]);
        return response()->json(['success' => true]);
    }

    public function destroy(Position $position)
    {
        $this->authorizeItem($position);

        if ($position->employees()->exists()) {
            return back()->with('error', 'এই Position-এ Employee আছে, আগে সেগুলো সরান।');
        }

        $position->delete();
        return back()->with('success', 'Position deleted successfully.');
    }

    private function authorizeItem(Position $position): void
    {
        abort_unless($position->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}
