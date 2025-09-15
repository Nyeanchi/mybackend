<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = [
            [
                'landlord_id' => 2, // Pierre Mbarga
                'name' => 'Villa Moderne Bonanjo',
                'description' => 'Magnifique villa moderne avec vue sur le Wouri, entièrement équipée avec climatisation et génératrice.',
                'address' => '12 Avenue Charles de Gaulle, Bonanjo',
                'city_id' => 1, // Douala
                'region_id' => 1, // Littoral
                'type' => 'villa',
                'total_units' => 1,
                'available_units' => 0,
                'rent_amount' => 350000.00,
                'deposit_amount' => 700000.00,
                'currency' => 'FCFA',
                'amenities' => json_encode(['Climatisation', 'Génératrice', 'Parking', 'Jardin', 'Sécurité 24h']),
                'images' => json_encode(['/images/villa1-1.jpg', '/images/villa1-2.jpg']),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'landlord_id' => 2, // Pierre Mbarga
                'name' => 'Appartements Résidence Akwa',
                'description' => 'Résidence moderne au cœur d\'Akwa avec ascenseur et parking sécurisé.',
                'address' => '45 Rue des Palmiers, Akwa',
                'city_id' => 1, // Douala
                'region_id' => 1, // Littoral
                'type' => 'apartment',
                'total_units' => 8,
                'available_units' => 3,
                'rent_amount' => 180000.00,
                'deposit_amount' => 360000.00,
                'currency' => 'FCFA',
                'amenities' => json_encode(['Ascenseur', 'Parking', 'Sécurité', 'Génératrice']),
                'images' => json_encode(['/images/apt1-1.jpg', '/images/apt1-2.jpg']),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'landlord_id' => 3, // Marie Kouam
                'name' => 'Studios Bastos Premium',
                'description' => 'Studios meublés haut de gamme à Bastos, idéaux pour jeunes professionnels.',
                'address' => '23 Avenue Rosa Parks, Bastos',
                'city_id' => 4, // Yaoundé
                'region_id' => 2, // Centre
                'type' => 'studio',
                'total_units' => 12,
                'available_units' => 4,
                'rent_amount' => 120000.00,
                'deposit_amount' => 240000.00,
                'currency' => 'FCFA',
                'amenities' => json_encode(['Meublé', 'WiFi', 'Klimatisation', 'Kitchenette']),
                'images' => json_encode(['/images/studio1-1.jpg', '/images/studio1-2.jpg']),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'landlord_id' => 3, // Marie Kouam
                'name' => 'Villa Familiale Mendong',
                'description' => 'Grande villa familiale avec jardin spacieux, parfaite pour famille nombreuse.',
                'address' => '78 Route de Mendong',
                'city_id' => 4, // Yaoundé
                'region_id' => 2, // Centre
                'type' => 'house',
                'total_units' => 1,
                'available_units' => 1,
                'rent_amount' => 280000.00,
                'deposit_amount' => 560000.00,
                'currency' => 'FCFA',
                'amenities' => json_encode(['Grand jardin', 'Garage 2 voitures', 'Buanderie', 'Terrasse']),
                'images' => json_encode(['/images/house1-1.jpg', '/images/house1-2.jpg']),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('properties')->insert($properties);
    }
}
