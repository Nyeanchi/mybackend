<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'code'  => $this->code,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'cities'     => CityResource::collection($this->whenLoaded('cities')),
            'properties' => PropertyResource::collection($this->whenLoaded('properties')),

            // Statistics
            'statistics' => [
                'total_cities'     => $this->when($this->relationLoaded('cities'), $this->cities->count()),
                'total_properties' => $this->when($this->relationLoaded('properties'), $this->properties->count()),
            ],
        ];
    }
}
