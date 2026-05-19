<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'gateway', 'mobile', 'message', 'type', 'status', 'response',
    ];

    // SMS types
    const TYPES = [
        'general'         => 'General',
        'bill_due'        => 'Bill Due',
        'payment_confirm' => 'Payment Confirm',
        'suspend'         => 'Suspend Notice',
        'restore'         => 'Restore Notice',
        'welcome'         => 'Welcome',
    ];

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
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
