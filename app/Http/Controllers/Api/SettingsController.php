<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get system settings.
     */
    public function systemSettings()
    {
        $settings = SystemSetting::first();

        // Default values if no settings exist
        $defaultSettings = [
            'platform_name' => 'Domotena',
            'default_language' => 'fr',
            'default_currency' => 'FCFA',
            'default_timezone' => 'Africa/Douala',
            'description' => 'domotena_database',
            'currency_rates' => [],
            'fees' => 0.0,
            'features' => [],
            'auth_max_login_attempts' => 5,
            'auth_session_timeout' => 120,
            'password_policy' => [],
            'notif_channels' => [],
            'notif_types' => [],
        ];

        // Merge database settings with defaults
        $response = $settings ? $settings->toArray() : $defaultSettings;

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    /**
     * Update system settings.
     */

    public function updateSystemSettings(Request $request)
    {
        $validated = $request->validate([
            'platform_name' => 'nullable|string|max:255',
            'default_language' => 'nullable|string|max:10',
            'default_currency' => 'nullable|string|max:10',
            'default_timezone' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'currency_rates' => 'nullable|array',
            'fees' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'auth_max_login_attempts' => 'nullable|integer|min:1',
            'auth_session_timeout' => 'nullable|integer|min:1',
            'password_policy' => 'nullable|array',
            'notif_channels' => 'nullable|array',
            'notif_types' => 'nullable|array',
        ]);

        $settings = SystemSetting::first();

        if (!$settings) {
            // Create new settings with the authenticated admin as the creator
            $settings = SystemSetting::create(array_merge($validated, [
                'created_by' => auth()->id(),
            ]));
        } else {
            // Update existing settings
            $settings->update($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'System settings updated successfully.',
            'data' => $settings,
        ]);
    }
}
