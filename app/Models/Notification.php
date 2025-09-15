<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_id',
        'sender_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'priority',
        'action_url',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    // Accessors
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Methods
    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
        return $this;
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
        return $this;
    }

    public function isUnread()
    {
        return is_null($this->read_at);
    }

    public function isExpired()
    {
        return $this->is_expired;
    }

    public function isHighPriority()
    {
        return $this->priority === 'high';
    }

    // Static methods for creating specific notification types
    public static function createPaymentReminder($tenantId, $paymentId, $dueDate)
    {
        return self::create([
            'recipient_id' => $tenantId,
            'type' => 'payment_reminder',
            'title' => 'Payment Reminder',
            'message' => 'Your rent payment is due on ' . $dueDate->format('M d, Y'),
            'data' => ['payment_id' => $paymentId],
            'priority' => 'high',
            'action_url' => '/payments/' . $paymentId,
        ]);
    }

    public static function createMaintenanceUpdate($tenantId, $requestId, $status)
    {
        $statusMessages = [
            'in_progress' => 'Your maintenance request is now being processed',
            'completed' => 'Your maintenance request has been completed',
            'cancelled' => 'Your maintenance request has been cancelled',
        ];

        return self::create([
            'recipient_id' => $tenantId,
            'type' => 'maintenance_update',
            'title' => 'Maintenance Request Update',
            'message' => $statusMessages[$status] ?? 'Your maintenance request status has been updated',
            'data' => ['request_id' => $requestId, 'status' => $status],
            'priority' => 'medium',
            'action_url' => '/maintenance/' . $requestId,
        ]);
    }

    public static function createLeaseExpiry($tenantId, $leaseEndDate)
    {
        return self::create([
            'recipient_id' => $tenantId,
            'type' => 'lease_expiry',
            'title' => 'Lease Expiring Soon',
            'message' => 'Your lease will expire on ' . $leaseEndDate->format('M d, Y'),
            'data' => ['lease_end_date' => $leaseEndDate],
            'priority' => 'high',
            'expires_at' => $leaseEndDate,
        ]);
    }
}