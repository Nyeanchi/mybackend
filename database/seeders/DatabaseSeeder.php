<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RegionsSeeder::class,
            CitiesSeeder::class,
            PaymentMethodsSeeder::class,
            UsersSeeder::class,
            PropertiesSeeder::class,
            TenantsSeeder::class,
            PaymentsSeeder::class,
            NotificationSeeder::class,
            MaintenanceRequestsSeeder::class,
        ]);
    }
}
