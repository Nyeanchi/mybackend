<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'platform_name',
        'default_language',
        'default_currency',
        'default_timezone',
        'description',
        'currency_rates',
        'fees',
        'features',
        'auth_max_login_attempts',
        'auth_session_timeout',
        'password_policy',
        'notif_channels',
        'notif_types',
        'created_by',
    ];

    protected $casts = [
        'currency_rates' => 'array',
        'fees' => 'array',
        'features' => 'array',
        'password_policy' => 'array',
        'notif_channels' => 'array',
        'notif_types' => 'array',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
