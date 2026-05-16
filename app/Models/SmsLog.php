<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'phone', 'message', 'type',
        'status', 'gateway_response', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}

