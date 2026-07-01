<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminNotificationController extends Controller
{
    public function index()
    {
        $sentNotifications = Notification::where('sender_type', 'super_admin')
            ->latest()
            ->paginate(25);

        return view('super-admin.notifications.index', compact('sentNotifications'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'color'   => 'nullable|string|in:primary,success,warning,danger,info',
            'icon'    => 'nullable|string',
            'url'     => 'nullable|string',
        ]);

        $superAdminId = Auth::guard('super_admin')->id() ?? Auth::id();

        $count = NotificationService::broadcastToAllUsers(
            $data['title'],
            $data['message'],
            [
                'type'        => 'system',
                'icon'        => $data['icon'] ?? 'fa-bullhorn',
                'color'       => $data['color'] ?? 'primary',
                'url'         => $data['url'] ?? null,
                'sender_id'   => $superAdminId,
                'sender_type' => 'super_admin',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Notification sent to {$count} user(s).",
        ]);
    }
}
