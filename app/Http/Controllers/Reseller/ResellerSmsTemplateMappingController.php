<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ResellerSmsTemplate;
use App\Models\ResellerSmsTemplateMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSmsTemplateMappingController extends Controller
{
    public function update(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'messages'   => 'required|array',
            'messages.*' => 'required|string|max:500',
        ]);

        foreach ($request->input('messages') as $type => $body) {
            $mapping = ResellerSmsTemplateMapping::forReseller($resellerId)->where('type', $type)->first();
            if (!$mapping) continue; // unknown type or not this reseller's — ignore silently

            ResellerSmsTemplate::updateOrCreate(
                ['mac_reseller_id' => $resellerId, 'title' => $mapping->title],
                ['body' => $body, 'is_active' => true]
            );
        }

        return back()->with('success', 'Notification message গুলো সংরক্ষণ হয়েছে।');
    }
}
