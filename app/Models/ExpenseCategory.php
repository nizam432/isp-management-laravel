<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Boot — auto-generate slug from name
    // ──────────────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────────────────────────────

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns inline style string for badge background.
     * Usage in Blade: <span style="{{ $category->badgeStyle }}">...</span>
     */
    public function getBadgeStyleAttribute(): string
    {
        $hex = $this->color ?? '#6c757d';

        // Lighten hex to 20% opacity for background, use full color for text
        return "background-color:{$hex}22; color:{$hex}; font-weight:500;";
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Total expense amount for a given month (Y-m format).
     * Used in P&L report.
     */
    public function totalForMonth(string $month): float
    {
        [$year, $mon] = explode('-', $month);

        return (float) $this->expenses()
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $mon)
            ->sum('amount');
    }
}
