<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'payment_type' => $this->payment_type,
            'payment_period' => $this->payment_period,
            'due_date' => $this->due_date ? $this->pdue_date->format('Y-m-d') : null,
            'paid_at' => $this->paid_at ? $this->paid_at->toISOString() : null,
            'status' => $this->status,
            'transaction_reference' => $this->transaction_reference,
            'receipt_number' => $this->receipt_number,
            'notes' => $this->notes,
            'late_fee' => $this->late_fee,
            'days_overdue' => $this->days_overdue,
            'is_overdue' => $this->isOverdue(),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,



            // Relationships
            'tenant' => new UserResource($this->whenLoaded('tenant')),
            'property' => new PropertyResource($this->whenLoaded('property')),
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'processed_by' => new UserResource($this->whenLoaded('processedBy')),

            // Status indicators
            'status_color' => $this->getStatusColor(),
            'status_label' => $this->getStatusLabel(),
            'urgency_level' => $this->getUrgencyLevel(),

            // Actions available based on status and user role
            'available_actions' => $this->getAvailableActions($request->user()),
        ];
    }

    protected function getStatusColor()
    {
        return match ($this->status) {
            'completed' => 'success',
            'pending' => $this->isOverdue() ? 'destructive' : 'warning',
            'cancelled' => 'muted',
            'failed' => 'destructive',
            default => 'secondary'
        };
    }

    protected function getStatusLabel()
    {
        $labels = [
            'pending' => 'En attente',
            'completed' => 'Payé',
            'cancelled' => 'Annulé',
            'failed' => 'Échec',
        ];

        $label = $labels[$this->status] ?? $this->status;

        if ($this->status === 'pending' && $this->isOverdue()) {
            $label .= ' (En retard)';
        }

        return $label;
    }

    protected function getUrgencyLevel()
    {
        if ($this->status === 'completed') {
            return 'none';
        }

        if ($this->isOverdue()) {
            return $this->days_overdue > 30 ? 'critical' : 'high';
        }

        $daysUntilDue = now()->diffInDays($this->due_date, false);

        if ($daysUntilDue <= 3) {
            return 'medium';
        }

        return 'low';
    }

    protected function getAvailableActions($user)
    {
        $actions = [];

        if (!$user) {
            return $actions;
        }

        // Actions for pending payments
        if ($this->status === 'pending') {
            if ($user->isAdmin() || ($user->isLandlord() && $this->property->landlord_id === $user->id)) {
                $actions[] = 'mark_as_paid';
                $actions[] = 'edit';
                $actions[] = 'cancel';
            }

            if ($user->isTenant() && $this->tenant_id === $user->id) {
                $actions[] = 'view_details';
            }
        }

        // Actions for completed payments
        if ($this->status === 'completed') {
            $actions[] = 'view_receipt';
            $actions[] = 'download_receipt';
        }

        // Admin actions
        if ($user->isAdmin()) {
            $actions[] = 'view_details';
            if ($this->status !== 'completed') {
                $actions[] = 'delete';
            }
        }

        return $actions;
    }
}
