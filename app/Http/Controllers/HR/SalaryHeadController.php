<?php
namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Models\HR\SalaryHead;
use Illuminate\Http\Request;

class SalaryHeadController extends Controller
{
    public function index()
    {
        $heads = SalaryHead::latest()->get();
        return view('hr.salary-heads.index', compact('heads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:addition,deduction',
        ]);
        SalaryHead::create($request->only('name', 'type') + ['is_active' => true]);
        return back()->with('success', "Salary head '{$request->name}' created.");
    }

    public function update(Request $request, SalaryHead $salaryHead)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $salaryHead->update($request->only('name', 'type'));
        return back()->with('success', 'Salary head updated.');
    }

    public function destroy(SalaryHead $salaryHead)
    {
        $salaryHead->delete();
        return back()->with('success', 'Salary head deleted.');
    }
}