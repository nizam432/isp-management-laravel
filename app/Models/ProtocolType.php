<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProtocolType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'details', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
