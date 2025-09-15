<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme',
        'language',
        'notifications_enabled',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'payment_reminders',
        'maintenance_updates',
        'timezone',
        'currency_preference',
        'date_format',
        'privacy_settings',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'payment_reminders' => 'boolean',
        'maintenance_updates' => 'boolean',
        'privacy_settings' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Default settings
    public static function getDefaultSettings()
    {
        return [
            'theme' => 'light',
            'language' => 'fr',
            'notifications_enabled' => true,
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => true,
            'payment_reminders' => true,
            'maintenance_updates' => true,
            'timezone' => 'Africa/Douala',
            'currency_preference' => 'FCFA',
            'date_format' => 'dd/mm/yyyy',
            'privacy_settings' => [
                'show_profile' => true,
                'show_contact_info' => false,
                'allow_marketing' => false,
            ],
        ];
    }

    // Methods
    public function updateNotificationPreferences($preferences)
    {
        $this->update([
            'notifications_enabled' => $preferences['notifications_enabled'] ?? $this->notifications_enabled,
            'email_notifications' => $preferences['email_notifications'] ?? $this->email_notifications,
            'sms_notifications' => $preferences['sms_notifications'] ?? $this->sms_notifications,
            'push_notifications' => $preferences['push_notifications'] ?? $this->push_notifications,
            'payment_reminders' => $preferences['payment_reminders'] ?? $this->payment_reminders,
            'maintenance_updates' => $preferences['maintenance_updates'] ?? $this->maintenance_updates,
        ]);
    }

    public function shouldReceiveNotification($type)
    {
        if (!$this->notifications_enabled) {
            return false;
        }

        switch ($type) {
            case 'email':
                return $this->email_notifications;
            case 'sms':
                return $this->sms_notifications;
            case 'push':
                return $this->push_notifications;
            case 'payment_reminder':
                return $this->payment_reminders;
            case 'maintenance_update':
                return $this->maintenance_updates;
            default:
                return true;
        }
    }
}