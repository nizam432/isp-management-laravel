<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgentCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id', 'payment_id', 'amount', 'status', 'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    // Relations
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}