<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'area',
        'commission_rate',
        'balance',
        'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'commission_rate' => 'decimal:2',
        'balance'         => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function commissions()
    {
        return $this->hasMany(AgentCommission::class);
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
