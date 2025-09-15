<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'first_name' => 'Tenant1',
                'last_name' => 'tenant',
                'email' => 'Tenant1.com',
                'phone' => '+237623465432',
                'password' => Hash::make('tenant123'),
                'user_id' => 4, // Jean Fotso
                'property_id' => 1, // Villa Moderne Bonanjo
                'unit_number' => 'Villa A',
                'rent_amount' => 350000.00,
                'deposit_amount' => 700000.00,
                'lease_start' => '2024-01-01',
                'lease_end' => '2024-12-31',
                'move_out_date' => '2024-02-15',
                'move_in_date' => '2025-02-14',
                'status' => 'active',
                'notes' => 'Locataire exemplaire, paiements toujours à temps.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'first_name' => 'Tenant2',
                'last_name' => '',
                'email' => 'Tenant2@email.com',
                'phone' => '+237646565432',
                'password' => Hash::make('tenant123'),
                'user_id' => 5, // Grace Ndongo
                'property_id' => 2, // Appartements Résidence Akwa
                'unit_number' => 'Apt 5B',
                'rent_amount' => 180000.00,
                'deposit_amount' => 360000.00,
                'lease_start' => '2024-03-01',
                'lease_end' => '2025-02-28',
                'move_out_date' => '2024-02-15',
                'move_in_date' => '2025-02-14',
                'status' => 'active',
                'notes' => 'Professionnelle, très soigneuse.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'first_name' => 'Tenant3',
                'last_name' => '',
                'email' => 'Tenant3@email.com',
                'phone' => '+237646565432',
                'password' => Hash::make('tenant123'),
                'user_id' => 6, // Patrick Biya Jr
                'property_id' => 3, // Studios Bastos Premium
                'unit_number' => 'Studio 12A',
                'rent_amount' => 120000.00,
                'deposit_amount' => 240000.00,
                'lease_start' => '2024-02-15',
                'lease_end' => '2025-02-14',
                'move_out_date' => '2024-02-15',
                'move_in_date' => '2025-02-14',
                'status' => 'active',
                'notes' => 'Étudiant en master, parents garants.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('tenants')->insert($tenants);
    }
}
