<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerPaymentGatewaySetting extends Model
{
    protected $table    = 'reseller_payment_gateway_settings';
    protected $fillable = ['mac_reseller_id', 'gateway_slug', 'config', 'is_active', 'sandbox'];
    protected $casts    = ['config' => 'array', 'is_active' => 'boolean', 'sandbox' => 'boolean'];

    public function cfg(string $key, $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
