<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        // Previously missing: 'phone', 'customer_id', 'gateway_response', 'sent_at' —
        // these exist as real columns in sms_logs (confirmed via phpMyAdmin), but
        // without being in $fillable, Laravel's mass-assignment protection silently
        // dropped them from every SmsLog::create() call — including the 'phone' fix
        // added earlier in SmsService.php, which never actually took effect because
        // of this. Adding them here makes those fields actually save.
        'gateway', 'mobile', 'phone', 'customer_id', 'message', 'type',
        'status', 'response', 'gateway_response', 'sent_at',
    ];

    // SMS types
    const TYPES = [
        'general'           => 'General',
        'bill_due'          => 'Bill Due',
        'payment_confirm'   => 'Payment Confirm',
        'suspend'           => 'Suspend Notice',
        'restore'           => 'Restore Notice',
        'welcome'           => 'Welcome',
        'invoice_generated' => 'Invoice Generated',
    ];

    public function scopeSuccess($query)
    {
        // sms_logs.status is enum('sent','failed','pending') — 'success' is not
        // a valid value, so this scope previously never matched anything, and
        // the "Today Sent" stat on the SMS dashboard was always 0.
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
