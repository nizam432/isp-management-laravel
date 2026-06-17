<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacResellerNotice extends Model
{
    use SoftDeletes;

    protected $table = 'mac_reseller_notices';

    protected $fillable = [
        'reseller_id',
        'title',
        'details',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(MacReseller::class, 'reseller_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
