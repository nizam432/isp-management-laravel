<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Polling endpoint — শুধু unread count রিটার্ন করে (হালকা, ঘন ঘন কল হবে)
     * GET /notifications/unread-count
     */
    public function unreadCount()
    {
        $user = Auth::user();

        return response()->json([
            'count' => $user->appUnreadNotificationsCount(),
        ]);
    }

    /**
     * Bell icon click করলে dropdown এ latest notification list দেখানো
     * GET /notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->ajax() || $request->wantsJson()) {
            $notifications = $user->appNotifications()->limit(15)->get()->map(function ($n) {
                return [
                    'id'        => $n->id,
                    'title'     => $n->title,
                    'message'   => $n->message,
                    'icon'      => $n->icon,
                    'color'     => $n->color,
                    'url'       => $n->url,
                    'is_read'   => $n->is_read,
                    'time_ago'  => $n->time_ago,
                ];
            });

            return response()->json([
                'notifications' => $notifications,
                'unread_count'  => $user->appUnreadNotificationsCount(),
            ]);
        }

        // Full page list (চাইলে "View All" পেজ হিসেবে)
        $notifications = $user->appNotifications()->paginate(25);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * একটা নির্দিষ্ট notification read হিসেবে mark করো
     * POST /notifications/{notification}/read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->appNotifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * সব notification একসাথে read করে দাও
     * POST /notifications/read-all
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->appUnreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
