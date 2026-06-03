<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function positions()
    {
        return $this->hasMany(Position::class); // ← Position same namespace এ
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}