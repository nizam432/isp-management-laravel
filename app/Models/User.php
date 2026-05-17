<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use JeroenNoten\LaravelAdminLte\Http\Controllers\Auth\AuthController;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ── AdminLTE Required Methods ──────────────────────────

    /**
     * Get the admin panel profile URL.
     * Used by AdminLTE user menu.
     */
    public function adminlte_profile_url(): string
    {
        return 'dashboard'; // redirect to dashboard (no profile page yet)
    }

    /**
     * Get the user's avatar image URL.
     */
    public function adminlte_image(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=3c8dbc&color=fff';
    }

    /**
     * Get the user's description shown under the name in the menu.
     */
    public function adminlte_desc(): string
    {
        // Show the user's first role name
        return $this->roles->first()?->name ?? 'User';
    }

    // ── Relations ─────────────────────────────────────────

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

    // ── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
