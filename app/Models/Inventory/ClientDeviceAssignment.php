<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientDeviceAssignment extends Model
{
    use HasFactory;

    protected $table = 'inventory_client_device_assignments';

    protected $fillable = [
        'client_id',      // FK → existing customers table
        'product_id',
        'location_id',    // কোন store থেকে নেওয়া হলো
        'serial_no',      // device serial number
        'assigned_date',
        'return_date',    // null মানে এখনো assigned
        'assigned_by',
        'returned_to',    // কে return নিলো
        'note',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'return_date'   => 'date',
    ];

    // ── Relations ─────────────────────────────────────────────────

    /**
     * Existing Customer model এ connect
     */
    public function client()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'client_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function location()
    {
        return $this->belongsTo(StoreLocation::class, 'location_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by');
    }

    public function returnedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'returned_to');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereNull('return_date');
    }

    public function scopeReturned($query)
    {
        return $query->whereNotNull('return_date');
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isReturned(): bool
    {
        return !is_null($this->return_date);
    }

    public function isActive(): bool
    {
        return is_null($this->return_date);
    }
}
