<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * একটা নির্দিষ্ট recipient (User/Employee instance) কে notification পাঠাও।
     */
    public static function send($recipient, string $title, string $message, array $options = []): Notification
    {
        return Notification::create(array_merge([
            'notifiable_id'   => $recipient->id,
            'notifiable_type' => get_class($recipient),
            'type'            => $options['type'] ?? 'general',
            'icon'            => $options['icon'] ?? 'fa-bell',
            'color'           => $options['color'] ?? 'primary',
            'url'             => $options['url'] ?? null,
            'sender_id'       => $options['sender_id'] ?? null,
            'sender_type'     => $options['sender_type'] ?? null,
            'related_id'      => $options['related_id'] ?? null,
            'related_type'    => $options['related_type'] ?? null,
        ], [
            'title'   => $title,
            'message' => $message,
        ]));
    }

    /**
     * একসাথে অনেকজনকে (broadcast) notification পাঠাও।
     * $recipients একটা Collection বা Array of models হতে পারে।
     */
    public static function broadcast($recipients, string $title, string $message, array $options = []): int
    {
        $count = 0;
        foreach ($recipients as $recipient) {
            static::send($recipient, $title, $message, $options);
            $count++;
        }
        return $count;
    }

    /**
     * সব Admin/Employee (User model) কে broadcast করো —
     * Super Admin panel থেকে এটাই মূলত ব্যবহার হবে।
     */
    public static function broadcastToAllUsers(string $title, string $message, array $options = []): int
    {
        $users = User::all();
        return static::broadcast($users, $title, $message, $options);
    }
}
