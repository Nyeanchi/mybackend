<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            // Littoral Region (ID: 1)
            ['region_id' => 1, 'name' => 'Douala', 'name_fr' => 'Douala', 'latitude' => 4.0511, 'longitude' => 9.7679, 'is_major' => true, 'created_at' => now(),
                'updated_at' => now(),],
            ['region_id' => 1, 'name' => 'Nkongsamba', 'name_fr' => 'Nkongsamba', 'latitude' => 4.9548, 'longitude' => 9.9317, 'is_major' => false, 'created_at' => now(),
                'updated_at' => now(),],
            ['region_id' => 1, 'name' => 'Edéa', 'name_fr' => 'Edéa', 'latitude' => 3.8000, 'longitude' => 10.1333, 'is_major' => false, 'created_at' => now(),
                'updated_at' => now(),],

            // Centre Region (ID: 2)
            ['region_id' => 2, 'name' => 'Yaoundé', 'name_fr' => 'Yaoundé', 'latitude' => 3.8480, 'longitude' => 11.5021, 'is_major' => true, 'created_at' => now(),
                'updated_at' => now(),],
            ['region_id' => 2, 'name' => 'Mbalmayo', 'name_fr' => 'Mbalmayo', 'latitude' => 3.5167, 'longitude' => 11.5000, 'is_major' => false, 'created_at' => now(),
                'updated_at' => now(),],
            ['region_id' => 2, 'name' => 'Obala', 'name_fr' => 'Obala', 'latitude' => 4.1667, 'longitude' => 11.5333, 'is_major' => false, 'created_at' => now(),
                'updated_at' => now(),],

            // West Region (ID: 3)
            ['region_id' => 3, 'name' => 'Bafoussam', 'name_fr' => 'Bafoussam', 'latitude' => 5.4781, 'longitude' => 10.4167, 'is_major' => true, 'created_at' => now(),
                'updated_at' => now(),],
            ['region_id' => 3, 'name' => 'Dschang', 'name_fr' => 'Dschang', 'latitude' => 5.4500, 'longitude' => 10.0500, 'is_major' => false, 'created_at' => now(),
                'updated_at' => now(),],
            ['region_id' => 3, 'name' => 'Mbouda', 'name_fr' => 'Mbouda', 'latitude' => 5.6167, 'longitude' => 10.2500, 'is_major' => false, 'created_at' => now(), 'updated_at' => now(),],

            // Northwest Region (ID: 4)
            ['region_id' => 4, 'name' => 'Bamenda', 'name_fr' => 'Bamenda', 'latitude' => 5.9597, 'longitude' => 10.1481, 'is_major' => true, 'created_at' => now(),
                'updated_at' => now(),],
            ['region_id' => 4, 'name' => 'Kumbo', 'name_fr' => 'Kumbo', 'latitude' => 6.2067, 'longitude' => 10.6772, 'is_major' => false, 'created_at' => now(), 'updated_at' => now(),],

            // Southwest Region (ID: 5)
            ['region_id' => 5, 'name' => 'Buea', 'name_fr' => 'Buea', 'latitude' => 4.1559, 'longitude' => 9.2428, 'is_major' => true, 'created_at' => now(),   'updated_at' => now(),],
            ['region_id' => 5, 'name' => 'Limbe', 'name_fr' => 'Limbe', 'latitude' => 4.0186, 'longitude' => 9.1967, 'is_major' => false, 'created_at' => now(), 'updated_at' => now(),],
            ['region_id' => 5, 'name' => 'Kumba', 'name_fr' => 'Kumba', 'latitude' => 4.6364, 'longitude' => 9.4469, 'is_major' => false, 'created_at' => now(), 'updated_at' => now(),],
        ];

        DB::table('cities')->insert($cities);
    }
}
