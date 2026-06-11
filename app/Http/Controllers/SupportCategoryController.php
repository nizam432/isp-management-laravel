<?php

namespace App\Http\Controllers;

use App\Models\SupportCategory;
use App\Models\HR\Department;
use Illuminate\Http\Request;

class SupportCategoryController extends Controller
{
    public function index()
    {
        $categories  = SupportCategory::with('department')->latest()->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('support_categories.index', compact('categories', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'category_type' => 'required|in:for_everyone,only_for_office',
            'details'       => 'nullable|string',
        ]);

        $category = SupportCategory::create($request->all());
        $category->load('department');

        return response()->json([
            'success'  => true,
            'message'  => 'Category added successfully.',
            'category' => $this->formatRow($category),
        ]);
    }

    public function edit(SupportCategory $supportCategory)
    {
        return response()->json(['success' => true, 'category' => $supportCategory]);
    }

    public function update(Request $request, SupportCategory $supportCategory)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'category_type' => 'required|in:for_everyone,only_for_office',
            'details'       => 'nullable|string',
        ]);

        $supportCategory->update($request->all());
        $supportCategory->load('department');

        return response()->json([
            'success'  => true,
            'message'  => 'Category updated successfully.',
            'category' => $this->formatRow($supportCategory),
        ]);
    }

    public function destroy(SupportCategory $supportCategory)
    {
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
}
