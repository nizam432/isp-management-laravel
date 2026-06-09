<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Setting;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no', 'customer_id', 'package_id', 'month',
        'amount', 'discount', 'due_amount', 'due_date', 'status', 'notes',
        'period_start', 'period_end', 'billing_type',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'period_start' => 'date',
        'period_end'   => 'date',
        'amount'       => 'decimal:2',
        'discount'     => 'decimal:2',
        'due_amount'   => 'decimal:2',
    ];

    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    /**
     * Auto generate invoice number using prefix from settings.
     */
    public static function generateNumber()
    {
        $prefix = Setting::get('invoice_prefix', 'INV');
        $year   = date('Y');

        $last = self::where('invoice_no', 'like', $prefix . '-' . $year . '-%')
                    ->lockForUpdate()
                    ->orderByRaw('CAST(SUBSTRING_INDEX(invoice_no, "-", -1) AS UNSIGNED) DESC')
                    ->first();

        $number = $last ? (intval(substr($last->invoice_no, -4)) + 1) : 1;

        return $prefix . '-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate due date based on settings.
     */
    public static function calculateDueDate($fromDate = null): string
    {
        $dueDays = intval(Setting::get('invoice_due_days', 7));
        $from    = $fromDate ? \Carbon\Carbon::parse($fromDate) : now();
        return $from->addDays($dueDays)->toDateString();
    }

    /**
     * Get period label for display.
     * Date to Date: "15 Jun - 14 Jul 2026"
     * Monthly: "June 2026"
     */
    public function getPeriodLabelAttribute(): string
    {
        if ($this->billing_type === 'date_to_date' && $this->period_start && $this->period_end) {
            return $this->period_start->format('d M') . ' - ' . $this->period_end->format('d M Y');
        }

        if ($this->month) {
            return \Carbon\Carbon::createFromFormat('Y-m', $this->month)->format('F Y');
        }

        return '-';
    }

    // Total paid amount
    public function getTotalPaidAttribute()
    {
        return $this->payments->where('status', 'active')->sum('amount');
    }
}