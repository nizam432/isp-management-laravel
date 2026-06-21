<?php

namespace App\Observers;

use App\Models\ClientSupportTicket;
use App\Models\User;
use App\Services\NotificationService;

class ClientSupportTicketObserver
{
    /**
     * নতুন Client Support Ticket তৈরি হলে — সংশ্লিষ্ট department এর
     * employee দের (assignees) এবং প্রয়োজনে সব admin কে notify করো।
     *
     * নোট: এখানে "সংশ্লিষ্ট admin/employee" বলতে সহজ approach নিয়েছি —
     * সব User (admin/staff login করা সবাই) কে notify করছি। যদি শুধু
     * নির্দিষ্ট department/permission থাকা ইউজারদের পাঠাতে হয়, এখানে
     * filter যোগ করা যাবে (যেমন: ->whereHas('roles', fn($q) => ...))
     */
    public function created(ClientSupportTicket $ticket): void
    {
        $title   = 'New Support Ticket';
        $message = "Ticket #{$ticket->ticket_no} — \"{$ticket->subject}\" submitted by {$ticket->customer?->name}.";

        $recipients = User::all(); // চাইলে এখানে role/permission দিয়ে filter করুন

        NotificationService::broadcast($recipients, $title, $message, [
            'type'         => 'ticket',
            'icon'         => 'fa-ticket-alt',
            'color'        => $ticket->priority === 'urgent' ? 'danger' : 'warning',
            'url'          => route('client-support.chat', $ticket->id),
            'related_id'   => $ticket->id,
            'related_type' => ClientSupportTicket::class,
        ]);
    }

    /**
     * Ticket solve হলে — যিনি ticket তৈরি করেছিলেন (created_by) তাকে জানাও।
     */
    public function updated(ClientSupportTicket $ticket): void
    {
        if ($ticket->isDirty('status') && $ticket->status === 'solved' && $ticket->created_by) {
            $creator = User::find($ticket->created_by);
            if ($creator) {
                NotificationService::send($creator, 'Ticket Solved', "Ticket #{$ticket->ticket_no} has been solved.", [
                    'type'         => 'ticket',
                    'icon'         => 'fa-check-circle',
                    'color'        => 'success',
                    'url'          => route('client-support.chat', $ticket->id),
                    'related_id'   => $ticket->id,
                    'related_type' => ClientSupportTicket::class,
                ]);
            }
        }
    }
}
