<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'property_id' => $this->property_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => $this->status,
            'images' => $this->images,
            'estimated_cost' => $this->formatted_estimated_cost,
            'actual_cost' => $this->formatted_actual_cost,
            'assigned_to' => $this->assigned_to,
            'scheduled_date' => $this->scheduled_date,
            'completed_date' => $this->completed_date,
            'tenant_notes' => $this->tenant_notes,
            'admin_notes' => $this->admin_notes,
            'days_open' => $this->days_open,
            'is_overdue' => $this->is_overdue,
            'tenant' => new UserResource($this->whenLoaded('tenant')),
            'property' => new PropertyResource($this->whenLoaded('property')),
            'assigned_to_user' => new UserResource($this->whenLoaded('assignedTo')),
            'landlord' => new UserResource($this->property->landlord ?? null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}











// <!-- 

//    namespace App\Http\Resources;

//    use Illuminate\Http\Resources\Json\JsonResource;

//    class MaintenanceRequestResource extends JsonResource
//    {
//        public function toArray($request)
//        {
//            return [
//                'id' => $this->id,
//                'tenant_id' => $this->tenant_id,
//                'property_id' => $this->property_id,
//                'title' => $this->title,
//                'description' => $this->description,
//                'category' => $this->category,
//                'priority' => $this->priority,
//                'status' => $this->status,
//                'images' => $this->images,
//                'estimated_cost' => $this->formatted_estimated_cost,
//                'actual_cost' => $this->formatted_actual_cost,
//                'assigned_to' => $this->assigned_to,
//                'scheduled_date' => $this->scheduled_date,
//                'completed_date' => $this->completed_date,
//                'tenant_notes' => $this->tenant_notes,
//                'admin_notes' => $this->admin_notes,
//                'days_open' => $this->days_open,
//                'is_overdue' => $this->is_overdue,
//                'tenant' => new UserResource($this->whenLoaded('tenant')),
//                'property' => new PropertyResource($this->whenLoaded('property')),
//                'assigned_to_user' => new UserResource($this->whenLoaded('assignedTo')),
//                'created_at' => $this->created_at,
//                'updated_at' => $this->updated_at,
//            ];
//        }
//    }
//  -->
