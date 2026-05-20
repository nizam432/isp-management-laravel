<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'max_customers', 'max_routers',
        'sms_enabled', 'reseller_enabled', 'trial_days', 'is_active', 'description',
    ];

    protected $casts = [
        'sms_enabled'      => 'boolean',
        'reseller_enabled' => 'boolean',
        'is_active'        => 'boolean',
        'price'            => 'decimal:2',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getMaxCustomersLabelAttribute(): string
    {
        return $this->max_customers === -1 ? 'Unlimited' : (string) $this->max_customers;
    }

    public function getMaxRoutersLabelAttribute(): string
    {
        return $this->max_routers === -1 ? 'Unlimited' : (string) $this->max_routers;
    }
}