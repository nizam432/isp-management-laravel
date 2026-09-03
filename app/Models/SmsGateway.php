<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    /**
     * This table only exists in the CENTRAL database (Super Admin manages
     * which gateways are enabled/active). Without this, queries against this
     * model use whatever the CURRENT default connection is — which, from
     * inside a tenant's own request, is now correctly the tenant's own
     * database (tenancy switching was fixed) — where this table doesn't
     * exist. Hardcoding the connection makes this model always read from
     * central, regardless of tenant context.
     *
     * Note: logs() below still works correctly — SmsLog has no $connection
     * set, so it resolves to the CURRENT tenant connection independently;
     * Eloquent runs relationship queries per-model, not as a single
     * cross-database JOIN, so each tenant's own sms_logs are still matched
     * correctly against this centrally-stored gateway's slug.
     */
    protected $connection = 'mysql';

    protected $fillable = [
        'name', 'slug', 'is_active', 'is_enabled', 'config', 'description',
    ];
    protected $casts = [
        'is_active'  => 'boolean',
        'is_enabled' => 'boolean',
        'config'     => 'array',
    ];

    public function logs()
    {
        return $this->hasMany(SmsLog::class, 'gateway', 'slug');
    }

    // Super Admin এ enabled gateways
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}