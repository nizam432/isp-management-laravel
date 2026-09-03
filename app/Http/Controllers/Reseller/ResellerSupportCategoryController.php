<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\SupportCategory;
use App\Models\HR\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSupportCategoryController extends Controller
{
    public function index()
    {
        $resellerId  = Auth::guard('mac_reseller')->id();
        $categories  = SupportCategory::forReseller($resellerId)->with('department')->latest()->get();
        $departments = Department::forReseller($resellerId)->active()->orderBy('name')->get();

        return view('reseller.client-support.category.index', compact('categories', 'departments'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'name'          => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'category_type' => 'required|in:for_everyone,only_for_office',
            'details'       => 'nullable|string',
        ]);

        if ($request->department_id) {
            Department::forReseller($resellerId)->findOrFail($request->department_id);
        }

        $category = SupportCategory::create([
            'mac_reseller_id' => $resellerId,
            'name'            => $request->name,
            'department_id'   => $request->department_id,
            'category_type'   => $request->category_type,
            'details'         => $request->details,
        ]);
        $category->load('department');

        return response()->json([
            'success'  => true,
            'message'  => 'Category added successfully.',
            'category' => $this->formatRow($category),
        ]);
    }

    public function edit(SupportCategory $supportCategory)
    {
        $this->authorizeItem($supportCategory);
        return response()->json(['success' => true, 'category' => $supportCategory]);
    }

    public function update(Request $request, SupportCategory $supportCategory)
    {
        $this->authorizeItem($supportCategory);
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'name'          => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'category_type' => 'required|in:for_everyone,only_for_office',
            'details'       => 'nullable|string',
        ]);

        if ($request->department_id) {
            Department::forReseller($resellerId)->findOrFail($request->department_id);
        }

        $supportCategory->update($request->only(['name', 'department_id', 'category_type', 'details']));
        $supportCategory->load('department');

        return response()->json([
            'success'  => true,
            'message'  => 'Category updated successfully.',
            'category' => $this->formatRow($supportCategory),
        ]);
    }

    public function destroy(SupportCategory $supportCategory)
    {
        $this->authorizeItem($supportCategory);

        if ($supportCategory->tickets()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This category is already in use by one or more tickets. It cannot be deleted.',
            ], 422);
        }

        $supportCategory->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }

    private function formatRow(SupportCategory $cat): array
    {
        return [
            'id'            => $cat->id,
            'name'          => $cat->name,
            'department'    => $cat->department->name ?? '—',
            'department_id' => $cat->department_id,
            'category_type' => $cat->category_type,
            'type_label'    => $cat->category_type_label,
            'type_badge'    => $cat->category_type_badge,
            'details'       => $cat->details,
        ];
    }

    private function authorizeItem(SupportCategory $category): void
    {
        abort_unless($category->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}
