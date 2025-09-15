<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'lease_start' => $this->lease_start ? $this->lease_start->format('Y-m-d') : null,
            'lease_end' => $this->lease_end ? $this->lease_end->format('Y-m-d') : null,
            'rent_amount' => $this->rent_amount,
            'deposit_amount' => $this->deposit_amount,
            'status' => $this->status,
            'move_in_date' => $this->move_in_date ? $this->move_in_date->format('Y-m-d') : null,
            'move_out_date' => $this->move_out_date ? $this->move_out_date->format('Y-m-d') : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Computed properties
            'full_address' => $this->full_address,
            'lease_status' => $this->lease_status,
            'days_until_lease_expiry' => $this->days_until_lease_expiry,
            'is_lease_active' => $this->isLeaseActive(),
            'is_lease_expiring_soon' => $this->isLeaseExpiringSoon(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'property' => new PropertyResource($this->whenLoaded('property')),

            // Financial summary
            'financial_summary' => [
                'total_paid' => $this->getTotalPaid(),
                'outstanding_balance' => $this->getOutstandingBalance(),
                'rent_amount_formatted' => number_format($this->rent_amount, 0, ',', ' ') . ' FCFA',
                'deposit_formatted' => number_format($this->deposit_amount, 0, ',', ' ') . ' FCFA',
            ],

            // Recent activity (when loaded)
            'recent_payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'recent_maintenance' => MaintenanceRequestResource::collection($this->whenLoaded('maintenanceRequests')),

            // Statistics (when available)
            'statistics' => $this->when($this->relationLoaded('payments') || $this->relationLoaded('maintenanceRequests'), [
                'payment_history' => [
                    'total_payments' => $this->payments()->count() ?? 0,
                    'completed_payments' => $this->payments()->completed()->count() ?? 0,
                    'pending_payments' => $this->payments()->pending()->count() ?? 0,
                    'overdue_payments' => $this->payments()->overdue()->count() ?? 0,
                ],
                'maintenance_requests' => [
                    'total' => $this->maintenanceRequests()->count() ?? 0,
                    'pending' => $this->maintenanceRequests()->pending()->count() ?? 0,
                    'in_progress' => $this->maintenanceRequests()->inProgress()->count() ?? 0,
                    'completed' => $this->maintenanceRequests()->completed()->count() ?? 0,
                ],
            ]),

            // Status indicators
            'status_indicators' => [
                'lease_status_color' => $this->getLeaseStatusColor(),
                'lease_status_label' => $this->getLeaseStatusLabel(),
                'payment_status' => $this->getPaymentStatus(),
                'urgency_flags' => $this->getUrgencyFlags(),
            ],

            // Available actions based on user role and tenant status
            'available_actions' => $this->getAvailableActions($request->user()),
        ];
    }

    protected function getLeaseStatusColor()
    {
        return match ($this->lease_status) {
            'current' => 'success',
            'expiring_soon' => 'warning',
            'expired' => 'destructive',
            'future' => 'secondary',
            default => 'muted'
        };
    }

    protected function getLeaseStatusLabel()
    {
        $labels = [
            'current' => 'Bail actif',
            'expiring_soon' => 'Expire bientôt',
            'expired' => 'Expiré',
            'future' => 'À venir',
        ];

        return $labels[$this->lease_status] ?? $this->lease_status;
    }

    protected function getPaymentStatus()
    {
        $outstandingBalance = $this->getOutstandingBalance();

        if ($outstandingBalance > 0) {
            return [
                'status' => 'outstanding',
                'label' => 'Paiements en attente',
                'color' => 'warning',
                'amount' => $outstandingBalance,
            ];
        }

        return [
            'status' => 'up_to_date',
            'label' => 'À jour',
            'color' => 'success',
            'amount' => 0,
        ];
    }

    protected function getUrgencyFlags()
    {
        $flags = [];

        // Lease expiry flag
        if ($this->isLeaseExpiringSoon(30)) {
            $flags[] = [
                'type' => 'lease_expiry',
                'message' => 'Bail expire dans ' . abs($this->days_until_lease_expiry) . ' jours',
                'priority' => 'high',
            ];
        }

        // Outstanding payments flag
        if ($this->getOutstandingBalance() > 0) {
            $flags[] = [
                'type' => 'outstanding_payment',
                'message' => 'Paiements en attente',
                'priority' => 'medium',
            ];
        }

        return $flags;
    }

    protected function getAvailableActions($user)
    {
        $actions = [];

        if (!$user) {
            return $actions;
        }

        // Landlord actions
        if ($user->isLandlord() && $this->property && $this->property->landlord_id === $user->id) {
            $actions[] = 'view_details';
            $actions[] = 'edit';
            $actions[] = 'view_payments';
            $actions[] = 'create_payment';

            if ($this->status === 'active') {
                $actions[] = 'terminate_lease';
            }
        }

        // Tenant actions
        if ($user->isTenant() && $this->user_id === $user->id) {
            $actions[] = 'view_details';
            $actions[] = 'view_payments';
            $actions[] = 'create_maintenance_request';
        }

        // Admin actions
        if ($user->isAdmin()) {
            $actions[] = 'view_details';
            $actions[] = 'edit';
            $actions[] = 'delete';
            $actions[] = 'view_payments';
            $actions[] = 'create_payment';
        }

        return $actions;
    }
}
