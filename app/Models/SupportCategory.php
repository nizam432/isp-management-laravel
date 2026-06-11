<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupportCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id', 'category_type', 'details', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function department()
    {
        return $this->belongsTo(\App\Models\HR\Department::class);
    }

    public function tickets()
    {
        return $this->hasMany(ClientSupportTicket::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getCategoryTypeLabelAttribute(): string
    {
        return $this->category_type === 'for_everyone' ? 'For Everyone' : 'Only For Office';
    }

    public function getCategoryTypeBadgeAttribute(): string
    {
        return $this->category_type === 'for_everyone' ? 'success' : 'warning';
    }
}
