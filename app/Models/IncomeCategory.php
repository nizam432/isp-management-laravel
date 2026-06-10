<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class IncomeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'color', 'icon',
        'description', 'is_system', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ── Boot ──────────────────────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────
    public function incomes()
    {
        return $this->hasMany(Income::class, 'category_id');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Manual entry categories only (exclude Monthly Bill which is auto)
    public function scopeManual($query)
    {
        return $query->where('is_system', 0);
    }

    // ── Accessors ─────────────────────────────────────────────────
    public function getBadgeStyleAttribute(): string
    {
        $hex = $this->color ?? '#185FA5';
        return "background-color:{$hex}22; color:{$hex}; font-weight:500;";
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function totalForMonth(string $month): float
    {
        [$year, $mon] = explode('-', $month);
        return (float) $this->incomes()
            ->where('status', 'active')
            ->whereYear('income_date', $year)
            ->whereMonth('income_date', $mon)
            ->sum('amount');
    }
}
