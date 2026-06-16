<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    protected $table    = 'payment_gateway_settings';
    protected $fillable = ['tenant_id', 'gateway_slug', 'config', 'is_active', 'sandbox'];
    protected $casts    = ['config' => 'array', 'is_active' => 'boolean', 'sandbox' => 'boolean'];

    public function cfg(string $key, $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
