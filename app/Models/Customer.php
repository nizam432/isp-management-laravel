<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mikrotik_status', 'occupation', 'gender', 'zone_id', 'sub_zone_id',
        'connection_type_id', 'client_type_id', 'protocol_type_id', 'router_id',
        'billing_status', 'monthly_bill_amount', 'portal_password',
        'customer_code', 'name', 'phone', 'email', 'nid_number',
        'nid_photo', 'photo', 'address', 'area', 'package_id',
        'agent_id', 'connection_date', 'billing_date', 'status',
        'ip_address', 'mac_address', 'pppoe_username', 'pppoe_password',
        'remarks', 'created_by', 'advance_balance',
    ];

    protected $casts = [
        'connection_date' => 'date',
        'billing_date'    => 'integer',
        'advance_balance' => 'decimal:2',
    ];

    protected $hidden = [
        'pppoe_password',
        'portal_password',
    ];

    // ── Authenticatable overrides ──────────────────────────
    // Laravel এর default 'email'+'password' এর বদলে
    // আমরা 'customer_code' + 'portal_password' ব্যবহার করব

    public function getAuthIdentifierName(): string
    {
        return 'customer_code';
    }

    public function getAuthPassword(): string
    {
        return $this->portal_password;
    }

    // ── Relations ──────────────────────────────────────────

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(ClientSupportTicket::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    public function zone()
    {
        return $this->belongsTo(\App\Models\Zone::class);
    }

    public function subZone()
    {
        return $this->belongsTo(\App\Models\SubZone::class);
    }

    public function connectionType()
    {
        return $this->belongsTo(\App\Models\ConnectionType::class);
    }

    public function clientType()
    {
        return $this->belongsTo(\App\Models\ClientType::class);
    }

    public function protocolType()
    {
        return $this->belongsTo(\App\Models\ProtocolType::class);
    }

    public function router()
    {
        return $this->belongsTo(\App\Models\MikrotikRouter::class, 'router_id');
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeByArea($query, $area)
    {
        return $query->where('area', $area);
    }

    // ── Helpers ────────────────────────────────────────────

    public static function generateCode(): string
    {
        $prefix = \App\Models\Setting::get('customer_code_prefix', 'ISP');
        $last   = self::withTrashed()->orderByRaw('CAST(SUBSTRING_INDEX(customer_code, "-", -1) AS UNSIGNED) DESC')->first();
        $number = $last ? (intval(substr($last->customer_code, strrpos($last->customer_code, '-') + 1)) + 1) : 1;
        return $prefix . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
