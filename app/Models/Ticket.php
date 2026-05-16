<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no', 'customer_id', 'subject', 'description',
        'category', 'priority', 'status', 'assigned_to', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    // Auto generate ticket number
    public static function generateNumber()
    {
        $last = self::latest()->first();
        $number = $last ? (intval(substr($last->ticket_no, -4)) + 1) : 1;
        $year = date('Y');
        return 'TKT-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
