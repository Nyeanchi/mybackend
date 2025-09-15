<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'address',
        'city_id',
        'landlord_id',
        'total_units',
        'available_units',
        'rent_amount',
        'deposit_amount',
        'currency',
        'description',
        'amenities',
        'images',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'rent_amount' => 'decimal:2',
    ];
    protected static function booted()
    {
        static::creating(function ($property) {
            if ($property->city_id && !$property->region_id) {
                $city = City::find($property->city_id);
                $property->region_id = $city?->region_id;
            }
        });
    }

    // Relationships
    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
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
    public function scopeAvailable($query)
    {
        return $query->where('available_units', '>', 0);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getFormattedRentAttribute()
    {
        return number_format($this->rent_amount, 0, ',', ' ') . ' ' . $this->currency;
    }

    public function getOccupancyRateAttribute()
    {
        if ($this->total_units == 0) return 0;
        $occupied = $this->total_units - $this->available_units;
        return round(($occupied / $this->total_units) * 100, 2);
    }

    // Methods
    public function hasAvailableUnits()
    {
        return $this->available_units > 0;
    }

    public function decrementAvailableUnits($count = 1)
    {
        if ($this->available_units >= $count) {
            $this->decrement('available_units', $count);
            return true;
        }
        return false;
    }

    public function incrementAvailableUnits($count = 1)
    {
        if ($this->available_units + $count <= $this->total_units) {
            $this->increment('available_units', $count);
            return true;
        }
        return false;
    }
}
