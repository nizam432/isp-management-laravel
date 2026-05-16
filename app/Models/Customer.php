<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code', 'name', 'phone', 'email', 'nid_number',
        'nid_photo', 'photo', 'address', 'area', 'package_id',
        'agent_id', 'connection_date', 'billing_date', 'status',
        'ip_address', 'mac_address', 'pppoe_username', 'pppoe_password',
        'remarks', 'created_by',
    ];

    protected $casts = [
        'connection_date' => 'date',
        'billing_date'    => 'integer',
    ];

    protected $hidden = [
        'pppoe_password',
    ];

    // Relations
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

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    // Scopes
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

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active'    => '<span class="badge bg-success">Active</span>',
            'inactive'  => '<span class="badge bg-secondary">Inactive</span>',
            'suspended' => '<span class="badge bg-warning">Suspended</span>',
            'expired'   => '<span class="badge bg-danger">Expired</span>',
            default     => $this->status,
        };
    }

    // Auto generate customer code
    public static function generateCode()
    {
        $last = self::withTrashed()->latest()->first();
        $number = $last ? (intval(substr($last->customer_code, 4)) + 1) : 1;
        return 'ISP-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}