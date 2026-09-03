<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\SalaryHead;
use Illuminate\Http\Request;

class SalaryHeadController extends Controller
{
    public function index()
    {
        $heads = SalaryHead::whereNull('mac_reseller_id')   // shudu isp-admin er salary heads
            ->latest()
            ->get();

        return view('hr.salary-heads.index', compact('heads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:addition,deduction',
        ]);

        SalaryHead::create($request->only('name', 'type') + [
            'is_active'       => true,
            'mac_reseller_id' => null,   // isp-admin er salary head hishebe create hobe
        ]);

        return back()->with('success', "Salary head '{$request->name}' created.");
    }

    public function update(Request $request, SalaryHead $salaryHead)
    {
        if (!is_null($salaryHead->mac_reseller_id)) {
            abort(403, 'Unauthorized access to this salary head.');
        }

        $request->validate(['name' => 'required|string|max:100']);
        $salaryHead->update($request->only('name', 'type'));

        return back()->with('success', 'Salary head updated.');
    }

    public function destroy(SalaryHead $salaryHead)
    {
        if (!is_null($salaryHead->mac_reseller_id)) {
            abort(403, 'Unauthorized access to this salary head.');
        }

        $salaryHead->delete();

        return back()->with('success', 'Salary head deleted.');
    }
}