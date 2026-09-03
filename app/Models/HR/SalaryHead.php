<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class SalaryHead extends Model
{
    protected $fillable = ['mac_reseller_id', 'name', 'type', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAddition($query)
    {
        return $query->where('type', 'addition');
    }

    public function scopeDeduction($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeForReseller($query, $macResellerId)
    {
        return $query->where('mac_reseller_id', $macResellerId);
    }
}
