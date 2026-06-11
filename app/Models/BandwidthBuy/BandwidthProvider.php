<?php

namespace App\Models\BandwidthBuy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BandwidthProvider extends Model
{
    use HasFactory;

    protected $table    = 'bandwidth_providers';
    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone_no',
        'document',
        'address',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────
    public function purchases()
    {
        return $this->hasMany(BandwidthPurchase::class, 'provider_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document ? asset('storage/' . $this->document) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
