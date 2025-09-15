<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'property_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'images',
        'estimated_cost',
        'actual_cost',
        'assigned_to',
        'scheduled_date',
        'completed_date',
        'tenant_notes',
        'admin_notes',
    ];

    protected $casts = [
        'images' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'scheduled_date' => 'datetime',
        'completed_date' => 'datetime',
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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_date', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // Accessors
    public function getFormattedEstimatedCostAttribute()
    {
        return $this->estimated_cost ? number_format($this->estimated_cost, 0, ',', ' ') . ' FCFA' : null;
    }

    public function getFormattedActualCostAttribute()
    {
        return $this->actual_cost ? number_format($this->actual_cost, 0, ',', ' ') . ' FCFA' : null;
    }

    public function getDaysOpenAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    public function getIsOverdueAttribute()
    {
        return $this->scheduled_date && 
               $this->scheduled_date < now() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    // Methods
    public function markAsInProgress($assignedTo = null, $scheduledDate = null, $estimatedCost = null)
    {
        $this->update([
            'status' => 'in_progress',
            'assigned_to' => $assignedTo ?? $this->assigned_to,
            'scheduled_date' => $scheduledDate ?? $this->scheduled_date,
            'estimated_cost' => $estimatedCost ?? $this->estimated_cost,
        ]);
    }

    public function markAsCompleted($actualCost = null, $adminNotes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_date' => now(),
            'actual_cost' => $actualCost ?? $this->actual_cost,
            'admin_notes' => $adminNotes ?? $this->admin_notes,
        ]);
    }

    public function isUrgent()
    {
        return $this->priority === 'urgent';
    }

    public function isOverdue()
    {
        return $this->is_overdue;
    }

    public function addImage($imagePath)
    {
        $images = $this->images ?? [];
        $images[] = $imagePath;
        $this->update(['images' => $images]);
    }

    public function removeImage($imagePath)
    {
        $images = $this->images ?? [];
        $images = array_filter($images, function($image) use ($imagePath) {
            return $image !== $imagePath;
        });
        $this->update(['images' => array_values($images)]);
    }
}