<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'property_id',
        'payment_method_id',
        'amount',
        'currency',
        'payment_type',
        'payment_period',
        'due_date',
        'paid_date',
        'status',
        'transaction_reference',
        'receipt_number',
        'notes',
        'late_fee',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', 'pending');
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    public function scopeRentPayments($query)
    {
        return $query->where('payment_type', 'rent');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', ' ') . ' ' . $this->currency;
    }

    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->late_fee;
    }

    public function getDaysOverdueAttribute()
    {
        if ($this->status === 'completed' || $this->due_date >= now()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    // Methods
    public function isOverdue()
    {
        return $this->due_date < now() && $this->status === 'pending';
    }

    public function markAsPaid($paymentMethodId = null, $transactionRef = null, $processedBy = null)
    {
        $this->update([
            'status' => 'completed',
            'paid_date' => now(),
            'payment_method_id' => $paymentMethodId ?? $this->payment_method_id,
            'transaction_reference' => $transactionRef ?? $this->transaction_reference,
            'processed_by' => $processedBy,
            'receipt_number' => $this->generateReceiptNumber(),
        ]);
    }

    public function calculateLateFee($rate = 0.05)
    {
        if ($this->isOverdue()) {
            $daysOverdue = $this->days_overdue;
            $lateFee = $this->amount * $rate * ceil($daysOverdue / 30);
            return round($lateFee, 2);
        }
        return 0;
    }

    private function generateReceiptNumber()
    {
        $prefix = 'RCP';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }
}