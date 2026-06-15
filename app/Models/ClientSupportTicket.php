<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class ClientSupportTicket extends Model
{
    use HasFactory;

    protected $table = 'client_support_tickets';

    protected $fillable = [
        'ticket_no', 'customer_id', 'support_category_id', 'priority',
        'status', 'complained_no', 'subject', 'remarks', 'attachment',
        'send_sms', 'created_from', 'created_by', 'solved_by', 'solved_at',
    ];

    protected $casts = [
        'send_sms'  => 'boolean',
        'solved_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function category()
    {
        return $this->belongsTo(SupportCategory::class, 'support_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function solvedBy()
    {
        return $this->belongsTo(User::class, 'solved_by');
    }

    public function assignees()
    {
        return $this->belongsToMany(\App\Models\HR\Employee::class, 'ticket_assignees', 'ticket_id', 'employee_id')
                    ->withTimestamps();
    }

    // ── Client portal discussion replies ──────
    public function replies()
    {
        return $this->hasMany(ClientTicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    // ── Scopes ────────────────────────────────

    public function scopePending($query)    { return $query->where('status', 'pending'); }
    public function scopeProcessing($query) { return $query->where('status', 'processing'); }
    public function scopeSolved($query)     { return $query->where('status', 'solved'); }

    // ── Helpers ───────────────────────────────

    public static function generateNumber(): string
    {
        $last   = self::latest()->first();
        $number = $last ? (intval(substr($last->ticket_no, -4)) + 1) : 1;
        return 'TKT-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high'   => 'warning',
            'medium' => 'info',
            default  => 'secondary',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'danger',
            'processing' => 'warning',
            'solved'     => 'success',
            'closed'     => 'secondary',
            default      => 'secondary',
        };
    }
}
