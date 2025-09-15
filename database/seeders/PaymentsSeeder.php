<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = [
            // Jean Fotso payments (Villa)
            [
                'tenant_id' => 1,
                'property_id' => 1,
                'payment_method_id' => 1, // Orange Money
                'amount' => 350000.00,
                'late_fees' => 5250.00,

                'currency' => 'FCFA',
                'transaction_reference' => 'PAY-' . strtoupper(uniqid()),
                'notes' => 'Loyer Janvier 2024 - Villa Moderne Bonanjo',
                'paid_date' => '2024-01-01',

                'status' => 'completed',

            ],
            [
                'tenant_id' => 1,
                'property_id' => 1,
                'payment_method_id' => 2, // MTN MoMo
                'amount' => 350000.00,
                'late_fees' => 5250.00,

                'currency' => 'FCFA',
                'transaction_reference' => 'PAY-' . strtoupper(uniqid()),
                'notes' => 'Loyer Février 2024 - Villa Moderne Bonanjo',
                'paid_date' => '2024-02-01',
                'status' => 'completed',

            ],

            // Grace Ndongo payments (Apartment)
            [
                'tenant_id' => 2,
                'property_id' => 2,
                'payment_method_id' => 3, // Bank Transfer
                'amount' => 180000.00,
                'late_fees' => 0.00,

                'currency' => 'FCFA',
                'transaction_reference' => 'PAY-' . strtoupper(uniqid()),
                'notes' => 'Loyer Mars 2024 - Résidence Akwa Apt 5B',
                'paid_date' => '2024-03-01',

                'status' => 'completed',

            ],

            // Patrick Biya Jr payments (Studio)
            [
                'tenant_id' => 3,
                'property_id' => 3,
                'payment_method_id' => 1, // Orange Money
                'amount' => 120000.00,
                'late_fees' => 1800.00,

                'currency' => 'FCFA',
                'transaction_reference' => 'PAY-' . strtoupper(uniqid()),
                'notes' => 'Loyer Mars 2024 - Studio Bastos 12A',
                'paid_date' => '2024-03-01',

                'status' => 'completed',

            ],

            // Pending payment
            [
                'tenant_id' => 1,
                'property_id' => 1,
                'payment_method_id' => 1, // Orange Money
                'amount' => 350000.00,
                'late_fees' => 5250.00,

                'currency' => 'FCFA',
                'transaction_reference' => 'PAY-' . strtoupper(uniqid()),
                'notes' => 'Loyer Avril 2024 - Villa Moderne Bonanjo',
                'paid_date' => '2024-04-01',

                'status' => 'pending',

            ],
        ];

        DB::table('payments')->insert($payments);
    }
}
