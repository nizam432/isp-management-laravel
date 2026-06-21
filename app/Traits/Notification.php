<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * এই trait টা যেকোনো model এ ব্যবহার করলে সেই model আমাদের custom
 * Notification system (polling-based, bell icon) ব্যবহার করতে পারবে।
 *
 * ⚠️ গুরুত্বপূর্ণ: Laravel-এর built-in `Illuminate\Notifications\Notifiable`
 * trait-এ আগে থেকেই `notifications()`, `unreadNotifications()`, এবং
 * `notify()` নামে method আছে। সেই trait-এর সাথে conflict এড়াতে এখানে
 * সব method-এর নাম "app" prefix দিয়ে আলাদা রাখা হয়েছে:
 *
 *   appNotifications()         (নাম-সংঘর্ষ এড়াতে, Laravel-এর notifications() না)
 *   appUnreadNotifications()
 *   appUnreadNotificationsCount()
 *   notifyApp()
 *
 * ব্যবহার:
 *   class User extends Authenticatable {
 *       use \Illuminate\Notifications\Notifiable; // Laravel-এর built-in (অপরিবর্তিত)
 *       use \App\Traits\HasNotifications;          // আমাদের custom polling system
 *   }
 */
trait HasNotifications
{
    /**
     * এই user/employee এর সব notification (পড়া + না পড়া) — আমাদের custom system থেকে
     */
    public function appNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /**
     * শুধু unread notification
     */
    public function appUnreadNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->whereNull('read_at')->latest();
    }

    /**
     * Unread notification এর সংখ্যা — bell icon এর badge এর জন্য
     */
    public function appUnreadNotificationsCount(): int
    {
        return $this->appUnreadNotifications()->count();
    }

    /**
     * এই user/employee কে notification পাঠানো (instance method)
     */
    public function notifyApp(string $title, string $message, array $options = []): Notification
    {
        return Notification::create(array_merge([
            'notifiable_id'   => $this->id,
            'notifiable_type' => static::class,
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
}
