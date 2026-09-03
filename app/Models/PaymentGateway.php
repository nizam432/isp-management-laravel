<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    /**
     * This table only exists in the CENTRAL database (Super Admin manages
     * which gateways are enabled). Without this, queries against this model
     * use whatever the CURRENT default connection is — which, from inside
     * a tenant's own request, is now correctly the tenant's own database
     * (tenancy switching was fixed) — where this table doesn't exist.
     * Hardcoding the connection makes this model always read from central.
     */
    protected $connection = 'mysql';

    protected $table    = 'payment_gateways';
    protected $fillable = ['name', 'slug', 'description', 'type', 'is_enabled'];
    protected $casts    = ['is_enabled' => 'boolean'];

    public function scopeEnabled($q) { return $q->where('is_enabled', true); }
    public function scopeLocal($q)   { return $q->where('type', 'local'); }
    public function scopeInternational($q) { return $q->where('type', 'international'); }
}