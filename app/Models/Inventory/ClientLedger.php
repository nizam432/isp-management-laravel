<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientLedger extends Model
{
    use HasFactory;

    protected $table = 'inventory_client_ledger';

    protected $fillable = [
        'client_id',      // FK → existing customers table
        'date',
        'type',           // enum: sale, payment, return, adjustment
        'reference_id',   // sale_id / return_id
        'debit',          // payment পেলাম (বাকি কমলো)
        'credit',         // sale করলাম (বাকি বাড়লো)
        'balance',        // running balance
        'note',
        'created_by',
    ];

    protected $casts = [
        'date'    => 'date',
        'debit'   => 'decimal:2',
        'credit'  => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    /**
     * Existing Customer model এ connect
     */
    public function client()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'client_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    // ── Static Helpers ────────────────────────────────────────────

    /**
     * Client এর মোট due
     */
    public static function clientDue(int $clientId): float
    {
        $credit = self::where('client_id', $clientId)->sum('credit');
        $debit  = self::where('client_id', $clientId)->sum('debit');
        return (float) ($credit - $debit);
    }

    /**
     * Last balance of client
     */
    public static function lastBalance(int $clientId): float
    {
        $last = self::where('client_id', $clientId)
                    ->latest('id')
                    ->first();
        return $last ? (float) $last->balance : 0;
    }
}
