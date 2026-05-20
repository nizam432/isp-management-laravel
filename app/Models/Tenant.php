<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'address',
            'plan_id',
            'plan_expires_at',
            'is_active',
            'parent_id',    // Level 2 ISP এর জন্য
            'level',        // 1 = Independent, 2 = Reseller ISP
        ];
    }

    // Relations
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function parent()
    {
        return $this->belongsTo(Tenant::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Tenant::class, 'parent_id');
    }

    // Plan limits check
    public function canAddCustomer(int $currentCount): bool
    {
        $max = $this->plan->max_customers ?? 25;
        return $max === -1 || $currentCount < $max;
    }

    public function canAddRouter(int $currentCount): bool
    {
        $max = $this->plan->max_routers ?? 1;
        return $max === -1 || $currentCount < $max;
    }

    public function hasSms(): bool
    {
        return $this->plan->sms_enabled ?? false;
    }

    public function hasReseller(): bool
    {
        return $this->plan->reseller_enabled ?? false;
    }

    public function isExpired(): bool
    {
        return $this->plan_expires_at && now()->isAfter($this->plan_expires_at);
    }
}