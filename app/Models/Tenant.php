<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'user_id',
        'property_id',
        'unit_number',
        'lease_start',
        'lease_end',
        'rent_amount',
        'deposit_amount',
        'status',
        'move_in_date',
        'move_out_date',
        'notes',
    ];

    protected $casts = [
        'lease_start' => 'date',
        'lease_end' => 'date',
        'move_in_date' => 'date',
        'move_out_date' => 'date',
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeCurrentLeases($query)
    {
        return $query->where('lease_start', '<=', now())
                    ->where('lease_end', '>=', now());
    }

    public function scopeExpiringLeases($query, $days = 30)
    {
        return $query->where('lease_end', '<=', now()->addDays($days))
                    ->where('lease_end', '>=', now());
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        return $this->property->address . ', Unit ' . $this->unit_number;
    }

    public function getDaysUntilLeaseExpiryAttribute()
    {
        return now()->diffInDays($this->lease_end, false);
    }

    public function getLeaseStatusAttribute()
    {
        if ($this->lease_end < now()) {
            return 'expired';
        } elseif ($this->lease_end <= now()->addDays(30)) {
            return 'expiring_soon';
        } elseif ($this->lease_start <= now()) {
            return 'current';
        } else {
            return 'future';
        }
    }

    // Methods
    public function isLeaseActive()
    {
        return $this->lease_start <= now() && $this->lease_end >= now();
    }

    public function isLeaseExpiringSoon($days = 30)
    {
        return $this->lease_end <= now()->addDays($days) && $this->lease_end >= now();
    }

    public function getTotalPaid()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getOutstandingBalance()
    {
        return $this->payments()->where('status', 'pending')->sum('amount');
    }
}
