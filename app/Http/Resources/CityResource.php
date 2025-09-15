<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'region_id' => $this->region_id,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'region' => new RegionResource($this->whenLoaded('region')),
            'properties' => PropertyResource::collection($this->whenLoaded('properties')),
            'users' => UserResource::collection($this->whenLoaded('users')),

            // Optional statistics (example: number of properties, number of users)
            'statistics' => [
                'total_properties' => $this->when($this->relationLoaded('properties'), $this->properties->count()),
                'total_users' => $this->when($this->relationLoaded('users'), $this->users->count()),
            ],
        ];
    }
}
