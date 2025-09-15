<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Admin Users
            [
                'first_name' => 'System Administrator',
                'last_name' => '',
                'email' => 'admin@domotena.com',
                'phone' => '+237677123456',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Landlord Users
            [
                'first_name' => 'Pierre',
                'last_name' => 'Mbarga',
                'email' => 'pierre.mbarga@email.com',
                'phone' => '+237691234567',
                'password' => Hash::make('landlord123'),
                'role' => 'landlord',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Marie Kouam',
                'last_name' => 'Kouam',
                'email' => 'marie.kouam@email.com',
                'phone' => '+237677345678',
                'password' => Hash::make('landlord123'),
                'role' => 'landlord',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Tenant Users
            [
                'first_name' => 'Jean Fotso',
                'last_name' => '',
                'email' => 'jean.fotso@email.com',
                'phone' => '+237654321098',
                'password' => Hash::make('tenant123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Grace Ndongo',
                'last_name' => '',
                'email' => 'grace.ndongo@email.com',
                'phone' => '+237698765432',
                'password' => Hash::make('tenant123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Patrick Biya Jr',
                'last_name' => '',
                'email' => 'patrick.biya@email.com',
                'phone' => '+237612345678',
                'password' => Hash::make('tenant123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
