<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\HR\SalaryHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerHrSalaryHeadController extends Controller
{
    public function index()
    {
        $resellerId   = Auth::guard('mac_reseller')->id();
        $salaryHeads  = SalaryHead::forReseller($resellerId)->orderBy('name')->get();

        return view('reseller.hr.salary-head.index', compact('salaryHeads'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:addition,deduction',
        ]);

        SalaryHead::create([
            'mac_reseller_id' => $resellerId,
            'name'            => $data['name'],
            'type'            => $data['type'],
            'is_active'       => true,
        ]);

        return back()->with('success', 'Salary Head added successfully.');
    }

    public function update(Request $request, SalaryHead $salaryHead)
    {
        $this->authorizeItem($salaryHead);

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:addition,deduction',
            'is_active' => 'nullable|boolean',
        ]);

        $salaryHead->update([
            'name'      => $data['name'],
            'type'      => $data['type'],
            'is_active' => $request->boolean('is_active', $salaryHead->is_active),
        ]);

        return back()->with('success', 'Salary Head updated successfully.');
    }

    public function toggle(SalaryHead $salaryHead)
    {
        $this->authorizeItem($salaryHead);
        $salaryHead->update(['is_active' => !$salaryHead->is_active]);
        return response()->json(['success' => true]);
    }

    public function destroy(SalaryHead $salaryHead)
    {
        $this->authorizeItem($salaryHead);
        $salaryHead->delete();
        return back()->with('success', 'Salary Head deleted successfully.');
    }

    private function authorizeItem(SalaryHead $salaryHead): void
    {
        abort_unless($salaryHead->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}
