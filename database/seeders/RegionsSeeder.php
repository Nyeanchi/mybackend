<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['name' => 'Littoral', 'name_fr' => 'Littoral', 'code' => 'LT', 'country' => 'CM', ],
            ['name' => 'Centre', 'name_fr' => 'Centre', 'code' => 'CE', 'country' => 'CM'],
            ['name' => 'West', 'name_fr' => 'Ouest', 'code' => 'OU', 'country' => 'CM'],
            ['name' => 'Northwest', 'name_fr' => 'Nord-Ouest', 'code' => 'NW', 'country' => 'CM'],
            ['name' => 'Southwest', 'name_fr' => 'Sud-Ouest', 'code' => 'SW', 'country' => 'CM'],
            ['name' => 'South', 'name_fr' => 'Sud', 'code' => 'SU', 'country' => 'CM'],
            ['name' => 'East', 'name_fr' => 'Est', 'code' => 'ES', 'country' => 'CM'],
            ['name' => 'North', 'name_fr' => 'Nord', 'code' => 'NO', 'country' => 'CM'],
            ['name' => 'Far North', 'name_fr' => 'Extrême-Nord', 'code' => 'EN', 'country' => 'CM'],
            ['name' => 'Adamawa', 'name_fr' => 'Adamaoua', 'code' => 'AD', 'country' => 'CM'],
        ];

        DB::table('regions')->insert($regions);
    }
}
