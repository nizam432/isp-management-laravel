<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
 
class Position extends Model
{
    protected $fillable = ['department_id', 'name', 'description', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];
 
    public function department()
    {
        return $this->belongsTo(Department::class);
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
 