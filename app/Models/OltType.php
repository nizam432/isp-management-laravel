<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OltType extends Model
{
    protected $fillable = ['name', 'details', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function olts(): HasMany
    {
        return $this->hasMany(Olt::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
