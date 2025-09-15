<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Retrieve users created by UsersSeeder.php
        $admin = User::where('email', 'admin@domotena.com')->first();
        $landlord1 = User::where('email', 'pierre.mbarga@email.com')->first();
        $landlord2 = User::where('email', 'marie.kouam@email.com')->first();
        $tenant1 = User::where('email', 'jean.fotso@email.com')->first();
        $tenant2 = User::where('email', 'grace.ndongo@email.com')->first();
        $tenant3 = User::where('email', 'patrick.biya@email.com')->first();

        // Ensure all users exist
        if (!$admin || !$landlord1 || !$landlord2 || !$tenant1 || !$tenant2 || !$tenant3) {
            throw new \Exception('One or more users from UsersSeeder not found. Please run UsersSeeder first.');
        }

        // Create notifications using the Notification model's static methods
        Notification::createPaymentReminder(
            $tenant1->id,
            1,
            Carbon::today()->addDays(7)
        );

        Notification::createPaymentReminder(
            $tenant2->id,
            2,
            Carbon::today()->addDays(3)
        );

        Notification::createMaintenanceUpdate(
            $tenant1->id,
            1,
            'in_progress'
        );

        Notification::createMaintenanceUpdate(
            $tenant3->id,
            2,
            'completed'
        );

        Notification::createLeaseExpiry(
            $tenant2->id,
            Carbon::today()->addDays(30)
        );

        // Additional custom notifications
        Notification::create([
            'recipient_id' => $landlord1->id,
            'sender_id' => $admin->id,
            'type' => 'property_update',
            'title' => 'Property Verification Required',
            'message' => 'Your property listing at 123 Yaoundé St. requires verification.',
            'data' => ['property_id' => 1],
            'priority' => 'high',
            'action_url' => '/properties/1/verify',
            'read_at' => null,
            'expires_at' => Carbon::today()->addDays(14),
            'created_at' => Carbon::today()->subDays(2),
        ]);

        Notification::create([
            'recipient_id' => $landlord2->id,
            'sender_id' => $tenant1->id,
            'type' => 'tenant_query',
            'title' => 'Tenant Inquiry',
            'message' => 'Tenant Jean Fotso has a question about lease terms for your property.',
            'data' => ['tenant_id' => $tenant1->id, 'property_id' => 2],
            'priority' => 'low',
            'action_url' => '/tenants/' . $tenant1->id,
            'read_at' => null,
            'expires_at' => null,
            'created_at' => Carbon::today(),
        ]);

        Notification::create([
            'recipient_id' => $admin->id,
            'sender_id' => null,
            'type' => 'system_alert',
            'title' => 'System Maintenance Scheduled',
            'message' => 'Scheduled maintenance on Sep 10, 2025, from 2:00 AM to 4:00 AM WAT.',
            'data' => ['maintenance_date' => '2025-09-10'],
            'priority' => 'medium',
            'action_url' => null,
            'read_at' => Carbon::today()->subDay(),
            'expires_at' => Carbon::today()->addDays(3),
            'created_at' => Carbon::today()->subDays(3),
        ]);

        Notification::create([
            'recipient_id' => $tenant3->id,
            'sender_id' => $landlord2->id,
            'type' => 'welcome_message',
            'title' => 'Welcome to Your New Home',
            'message' => 'Welcome to your new rental property managed by Marie Kouam.',
            'data' => ['property_id' => 3],
            'priority' => 'low',
            'action_url' => '/properties/3',
            'read_at' => Carbon::today(),
            'expires_at' => null,
            'created_at' => Carbon::today()->subDays(1),
        ]);
    }
}
