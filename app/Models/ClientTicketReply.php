<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientTicketReply extends Model
{
    use HasFactory;

    protected $table = 'client_ticket_replies';

    protected $fillable = [
        'ticket_id', 'customer_id', 'user_id', 'message', 'attachment', 'sender_type',
    ];

    public function ticket()
    {
        return $this->belongsTo(ClientSupportTicket::class, 'ticket_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Sender name helper
    public function getSenderNameAttribute(): string
    {
        if ($this->sender_type === 'admin') {
            return $this->user->name ?? 'Admin';
        }

        // Reseller portal replies — sent by the reseller (owner or staff), not an
        // Admin User and not the Customer themself. There's no reseller_id column
        // on this table, so fall back to the ticket's own customer -> reseller link.
        if ($this->sender_type === 'reseller') {
            return $this->ticket?->customer?->macReseller?->business_name ?? 'Reseller Support';
        }

        return $this->customer->name ?? 'Customer';
    }
}
