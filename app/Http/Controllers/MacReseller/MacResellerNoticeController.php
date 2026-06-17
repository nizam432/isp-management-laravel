<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacReseller;
use App\Models\MacResellerNotice;
use Illuminate\Http\Request;

class MacResellerNoticeController extends Controller
{
    public function index()
    {
        $notices   = MacResellerNotice::with('reseller')->latest()->paginate(10);
        $resellers = MacReseller::orderBy('business_name')->get();
        return view('mac-reseller.notice.index', compact('notices', 'resellers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reseller_id' => 'nullable|exists:mac_resellers,id',
            'title'       => 'required|string|max:255',
            'details'     => 'required|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['created_by'] = auth()->id();
        $data['is_active']  = $request->boolean('is_active', true);

        MacResellerNotice::create($data);
        return response()->json(['success' => true, 'message' => 'Notice added.']);
    }

    public function edit(MacResellerNotice $notice)
    {
        return response()->json($notice);
    }

    public function update(Request $request, MacResellerNotice $notice)
    {
        $data = $request->validate([
            'reseller_id' => 'nullable|exists:mac_resellers,id',
            'title'       => 'required|string|max:255',
            'details'     => 'required|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $notice->update($data);
        return response()->json(['success' => true, 'message' => 'Notice updated.']);
    }

    public function destroy(MacResellerNotice $notice)
    {
        $notice->delete();
        return response()->json(['success' => true]);
    }

    public function toggle(MacResellerNotice $notice)
    {
        $notice->update(['is_active' => !$notice->is_active]);
        return response()->json(['success' => true]);
    }
}
