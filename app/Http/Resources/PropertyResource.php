<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'total_units' => $this->total_units,
            'available_units' => $this->available_units,
            'occupied_units' => $this->total_units - $this->available_units,
            'rent_amount' => $this->rent_amount,
            'formatted_rent' => $this->formatted_rent,
            'currency' => $this->currency,
            'description' => $this->description,
            'amenities' => $this->amenities ?? [],
            'images' => $this->images ?? [],
            'status' => $this->status,
            'occupancy_rate' => $this->occupancy_rate,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'landlord' => new UserResource($this->whenLoaded('landlord')),
            'city' => new CityResource($this->whenLoaded('city')),
            'tenants' => TenantResource::collection($this->whenLoaded('tenants')),

            // Statistics (when loaded)
            'statistics' => $this->when($this->relationLoaded('payments') || $this->relationLoaded('maintenanceRequests'), [
                'monthly_revenue' => $this->payments()
                    ->completed()
                    ->whereMonth('paid_date', now()->month)
                    ->whereYear('paid_date', now()->year)
                    ->sum('amount') ?? 0,
                'total_revenue' => $this->payments()->completed()->sum('amount') ?? 0,
                'pending_payments' => $this->payments()->pending()->sum('amount') ?? 0,
                'overdue_payments' => $this->payments()->overdue()->sum('amount') ?? 0,
                'maintenance_requests_count' => $this->maintenanceRequests()->count() ?? 0,
                'pending_maintenance' => $this->maintenanceRequests()->pending()->count() ?? 0,
            ]),

            // Recent activity (limited data)
            'recent_payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'recent_maintenance' => MaintenanceRequestResource::collection($this->whenLoaded('maintenanceRequests')),

            // Availability status
            'is_available' => $this->hasAvailableUnits(),
            'availability_status' => $this->available_units > 0 ? 'available' : 'full',
        ];
    }
}