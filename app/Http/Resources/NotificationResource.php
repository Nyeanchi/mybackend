<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'recipient_id' => $this->recipient_id,
            'sender_id' => $this->sender_id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'priority' => $this->priority,
            'action_url' => $this->action_url,
            'is_read' => $this->is_read,
            'is_expired' => $this->is_expired,
            'time_ago' => $this->time_ago,
            'created_at' => $this->created_at,
            'read_at' => $this->read_at,
            'expires_at' => $this->expires_at,
            'recipient' => new UserResource($this->whenLoaded('recipient')),
            'sender' => new UserResource($this->whenLoaded('sender')),
        ];
    }
}
