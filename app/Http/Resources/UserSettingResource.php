<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            
            // Preferences
            'theme' => $this->theme,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'currency_preference' => $this->currency_preference,
            'date_format' => $this->date_format,

            // Notifications
            'notifications_enabled' => $this->notifications_enabled,
            'email_notifications' => $this->email_notifications,
            'sms_notifications' => $this->sms_notifications,
            'push_notifications' => $this->push_notifications,
            'payment_reminders' => $this->payment_reminders,
            'maintenance_updates' => $this->maintenance_updates,

            // Privacy settings (JSON casted to array)
            'privacy_settings' => $this->privacy_settings ?? [
                'show_profile' => true,
                'show_contact_info' => false,
                'allow_marketing' => false,
            ],

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),

            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
