<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $table    = 'payment_gateways';
    protected $fillable = ['name', 'slug', 'description', 'type', 'is_enabled'];
    protected $casts    = ['is_enabled' => 'boolean'];

    public function scopeEnabled($q) { return $q->where('is_enabled', true); }
    public function scopeLocal($q)   { return $q->where('type', 'local'); }
    public function scopeInternational($q) { return $q->where('type', 'international'); }
}
