<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Orange Money',
                'name_fr' => 'Orange Money',
                'code' => 'orange_money',
                'provider' => 'Orange Cameroon',
                'fees_percent' => 0.0150, // 1.5%
                'fees_fixed' => 0.00,
                'currency' => 'FCFA',
                'requires_phone' => true,
                'is_active' => true,
                'icon_url' => '/icons/orange-money.png'
            ],
            [
                'name' => 'MTN Mobile Money',
                'name_fr' => 'MTN Mobile Money',
                'code' => 'mtn_momo',
                'provider' => 'MTN Cameroon',
                'fees_percent' => 0.0150, // 1.5%
                'fees_fixed' => 0.00,
                'currency' => 'FCFA',
                'requires_phone' => true,
                'is_active' => true,
                'icon_url' => '/icons/mtn-momo.png'
            ],
            [
                'name' => 'Bank Transfer',
                'name_fr' => 'Virement Bancaire',
                'code' => 'bank_transfer',
                'provider' => null,
                'fees_percent' => 0.0000,
                'fees_fixed' => 0.00,
                'currency' => 'FCFA',
                'requires_phone' => false,
                'is_active' => true,
                'icon_url' => '/icons/bank-transfer.png'
            ],
            [
                'name' => 'Cash Payment',
                'name_fr' => 'Paiement Espèces',
                'code' => 'cash',
                'provider' => null,
                'fees_percent' => 0.0000,
                'fees_fixed' => 0.00,
                'currency' => 'FCFA',
                'requires_phone' => false,
                'is_active' => true,
                'icon_url' => '/icons/cash.png'
            ],
        ];

        DB::table('payment_methods')->insert($paymentMethods);
    }
}
