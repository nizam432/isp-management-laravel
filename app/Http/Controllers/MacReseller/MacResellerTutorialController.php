<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\ResellerTutorial;
use Illuminate\Http\Request;

class MacResellerTutorialController extends Controller
{
    public function index()
    {
        $tutorials = ResellerTutorial::orderBy('sort_order')->orderByDesc('id')->get();
        return view('mac-reseller.tutorial.index', compact('tutorials'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'youtube_url' => 'required|url',
            'sort_order'  => 'nullable|integer',
        ]);

        $data['created_by'] = auth()->id();
        ResellerTutorial::create($data);

        return response()->json(['success' => true, 'message' => 'Tutorial added successfully.']);
    }

    public function edit(ResellerTutorial $tutorial)
    {
        return response()->json($tutorial);
    }

    public function update(Request $request, ResellerTutorial $tutorial)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'youtube_url' => 'required|url',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $tutorial->update($data);

        return response()->json(['success' => true, 'message' => 'Tutorial updated successfully.']);
    }

    public function destroy(ResellerTutorial $tutorial)
    {
        $tutorial->delete();
        return response()->json(['success' => true, 'message' => 'Tutorial removed.']);
    }

    public function toggle(ResellerTutorial $tutorial)
    {
        $tutorial->update(['is_active' => !$tutorial->is_active]);
        return response()->json(['success' => true]);
    }
}
