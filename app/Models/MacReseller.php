<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacReseller extends Model
{
    use SoftDeletes;

    protected $table = 'mac_resellers';

    protected $fillable = [
        'code',
        'contact_person',
        'email',
        'mobile',
        'phone',
        'national_id',
        'district',
        'upazila',
        'zone',
        'pop_prefix',
        'use_prefix_in_mikrotik_username',
        'pop_type',
        'min_rechargeable_amount',
        'address',
        'logo',
        'business_name',
        'tariff_id',
        'want_to_disable_clients',
        'min_balance',
        'username',
        'password',
        'allowed_menus',
        'level',
        'remaining_fund',
        'client_enabled',
        'fund_start',
        'is_locked',
        'restrict_online_payment',
        'is_active',
        'created_by',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'allowed_menus'                   => 'array',
        'use_prefix_in_mikrotik_username' => 'boolean',
        'want_to_disable_clients'         => 'boolean',
        'client_enabled'                  => 'boolean',
        'fund_start'                      => 'boolean',
        'is_locked'                       => 'boolean',
        'restrict_online_payment'         => 'boolean',
        'is_active'                       => 'boolean',
        'remaining_fund'                  => 'decimal:2',
        'min_balance'                     => 'decimal:2',
        'min_rechargeable_amount'         => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(MacResellerTariff::class, 'tariff_id');
    }

    public function fundings(): HasMany
    {
        return $this->hasMany(MacResellerFunding::class, 'reseller_id');
    }

    public function pgwPayments(): HasMany
    {
        return $this->hasMany(MacResellerPgwPayment::class, 'reseller_id');
    }

    public function pgwSettlements(): HasMany
    {
        return $this->hasMany(MacResellerPgwSettlement::class, 'reseller_id');
    }

    public function notices(): HasMany
    {
        return $this->hasMany(MacResellerNotice::class, 'reseller_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrepaid($query)
    {
        return $query->where('pop_type', 'prepaid');
    }

    public function scopePostpaid($query)
    {
        return $query->where('pop_type', 'postpaid');
    }

    // ── Helpers ───────────────────────────────────────────

    public static function generateCode(): string
    {
        $last = static::withTrashed()->max('id') ?? 0;
        return str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
