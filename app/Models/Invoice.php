<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no', 'customer_id', 'package_id', 'month',
        'amount', 'discount', 'due_amount', 'due_date', 'status', 'notes',
    ];

    protected $casts = [
        'due_date'   => 'date',
        'amount'     => 'decimal:2',
        'discount'   => 'decimal:2',
        'due_amount' => 'decimal:2',
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

    // Auto generate invoice number
    public static function generateNumber()
    {
        $last = self::latest()->first();
        $number = $last ? (intval(substr($last->invoice_no, -4)) + 1) : 1;
        $year = date('Y');
        return 'INV-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Total paid amount
    public function getTotalPaidAttribute()
    {
        return $this->payments->sum('amount');
    }
}

