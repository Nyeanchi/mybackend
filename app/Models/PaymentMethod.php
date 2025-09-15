<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    // Relationships
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeMobileMoney($query)
    {
        return $query->whereIn('type', ['orange_money', 'mtn_momo']);
    }

    // Methods
    public function isMobileMoney()
    {
        return in_array($this->type, ['orange_money', 'mtn_momo']);
    }

    public function isBank()
    {
        return $this->type === 'bank_transfer';
    }

    public function isCash()
    {
        return $this->type === 'cash';
    }
}