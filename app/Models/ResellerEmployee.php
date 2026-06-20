<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class ResellerEmployee extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $table = 'reseller_employees';

    protected $fillable = [
        'mac_reseller_id', 'name', 'email', 'phone', 'designation',
        'username', 'password', 'allowed_menus', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'allowed_menus' => 'array',
        'is_active'     => 'boolean',
    ];

    public function username()
    {
        return 'username';
    }

    public function isLoginAllowed(): bool
    {
        return $this->is_active && $this->macReseller && $this->macReseller->is_active && !$this->macReseller->is_locked;
    }

    public function canAccessMenu(string $menuKey): bool
    {
        $menus = $this->allowed_menus ?? [];
        return in_array(strtoupper($menuKey), array_map('strtoupper', $menus));
    }

    public function macReseller(): BelongsTo
    {
        return $this->belongsTo(MacReseller::class, 'mac_reseller_id');
    }
}
