<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'speed_download', 'speed_upload', 'data_limit',
        'price', 'connection_fee','client_type_id', 'btrc_price', 
        'btrc_bandwidth','mikrotik_profile','is_active', 'description',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'price'          => 'decimal:2',
        'connection_fee' => 'decimal:2',
    ];

    // Relations
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Accessors
    public function getSpeedLabelAttribute()
    {
        return $this->speed_download . 'Mbps / ' . $this->speed_upload . 'Mbps';
    }

    public function getDataLimitLabelAttribute()
    {
        return $this->data_limit == 0 ? 'Unlimited' : $this->data_limit . ' GB';
    }
    public function clientType()
    {
        return $this->belongsTo(\App\Models\ClientType::class)->withDefault(['name' => 'All']);
    }    
}


