<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'avatar' => $this->avatar,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'address' => $this->address,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'city' => new CityResource($this->whenLoaded('city')),
            'settings' => new UserSettingResource($this->whenLoaded('settings')),
            'properties' => PropertyResource::collection($this->whenLoaded('properties')),
            'tenant_profile' => new TenantResource($this->whenLoaded('tenantProfile')),

            // Conditional data based on role
            $this->mergeWhen($this->isLandlord(), [
                'properties_count' => $this->properties->count() ?? 0,
                'total_units' => $this->properties->sum('total_units') ?? 0,
                'occupied_units' => $this->properties->sum(function($property) {
                    return $property->total_units - $property->available_units;
                }) ?? 0,
            ]),

            $this->mergeWhen($this->isTenant(), [
                'current_lease' => new TenantResource($this->whenLoaded('tenantProfile')),
            ]),

            // Admin-only sensitive data
            $this->mergeWhen($request->user()?->isAdmin(), [
                'all_properties' => PropertyResource::collection($this->whenLoaded('properties')),
                'payment_history' => PaymentResource::collection($this->whenLoaded('payments')),
            ]),
        ];
    }
}