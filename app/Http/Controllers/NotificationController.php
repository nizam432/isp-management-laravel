<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** GET /notifications/unread-count — lightweight polling endpoint for the bell badge. */
    public function unreadCount()
    {
        $user = Auth::user();

        return response()->json([
            'count' => $user->appUnreadNotificationsCount(),
        ]);
    }

    /**
     * Return the latest notifications for the bell dropdown, or the full notification page.
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

        $notifications = $user->appNotifications()->paginate(25);
        return view('notifications.index', compact('notifications'));
    }

    /** POST /notifications/{id}/read — mark a single notification as read. */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->appNotifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /** POST /notifications/read-all — mark all notifications as read. */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->appUnreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
