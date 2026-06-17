<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacResellerPgwSettlement extends Model
{
    protected $table = 'mac_reseller_pgw_settlements';

    protected $fillable = [
        'reseller_id',
        'total_received',
        'settled_amount',
        'remaining_amount',
        'payment_status',
        'settlement_date',
        'settled_by',
        'remarks',
    ];

    protected $casts = [
        'settlement_date' => 'date',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(MacReseller::class, 'reseller_id');
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}
