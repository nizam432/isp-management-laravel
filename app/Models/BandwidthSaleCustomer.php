<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BandwidthSaleCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bandwidth_sale_customers';

    protected $fillable = [
        'customer_code', 'customer_name', 'contact_person',
        'email', 'mobile_number', 'phone_number',
        'pop_status', 'reference_by', 'address', 'remarks',
        'facebook_url', 'skype_id', 'website', 'photo',
        // Transmission
        'attn_info', 'vlan_info', 'bzr_dr_nas_id',
        'activation_date', 'ip_addresses', 'pop_info',
        // Login
        'username', 'password', 'activity_status',
        // Balance
        'balance_due', 'created_by',
    ];

    protected $casts = [
        'vlan_info'       => 'array',
        'ip_addresses'    => 'array',
        'activation_date' => 'date',
    ];

    protected $hidden = ['password'];

    // ── Boot: auto generate customer_code ─────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->customer_code)) {
                $model->customer_code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $last = self::withTrashed()->orderByDesc('id')->first();
        $seq  = $last ? ($last->id + 1) : 1;
        return 'BWC-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Relations ─────────────────────────────────────────────────
    public function invoices()
    {
        return $this->hasMany(BwsInvoice::class, 'bws_customer_id');
    }

    public function payments()
    {
        return $this->hasMany(BwsInvoicePayment::class, 'bws_customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('activity_status', 'active');
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function hasPurchase(): bool
    {
        return $this->invoices()->exists();
    }
}
