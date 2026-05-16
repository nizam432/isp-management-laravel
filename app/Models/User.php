<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // Relations
    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    public function createdCustomers()
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}