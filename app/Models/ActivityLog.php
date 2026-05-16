<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    public $updatedAt = false;

    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'old_values', 'new_values', 'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Static helper to log activity
    public static function log($action, $modelType = null, $modelId = null, $old = null, $new = null)
    {
        return self::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
        ]);
    }
}
