<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maintenanceRequests = [
            [
                'tenant_id' => 1, // Jean Fotso
                'property_id' => 1, // Villa Moderne Bonanjo
                'title' => 'Fuite d\'eau dans la cuisine',
                'description' => 'Il y a une petite fuite sous l\'évier de la cuisine qui crée une flaque d\'eau. Le problème semble venir du robinet principal.',
                'category' => 'plumbing',
                'priority' => 'medium',
                'status' => 'in_progress',
                'assigned_to' => 2, // Pierre Mbarga (landlord)
                'images' => json_encode(['/maintenance/leak1.jpg', '/maintenance/leak2.jpg']),
                'estimated_cost' => 25000.00,
                'actual_cost' => null,
                'completed_at' => null,
                'created_at' => '2024-03-15 08:30:00',
                'updated_at' => '2024-03-16 10:15:00',
            ],
            [
                'tenant_id' => 2, // Grace Ndongo
                'property_id' => 2, // Appartements Résidence Akwa
                'title' => 'Climatisation ne fonctionne plus',
                'description' => 'La climatisation de la chambre principale ne démarre plus depuis hier soir. Pas de bruit, aucune réaction.',
                'category' => 'hvac',
                'priority' => 'high',
                'status' => 'open',
                'assigned_to' => null,
                'images' => json_encode(['/maintenance/ac1.jpg']),
                'estimated_cost' => null,
                'actual_cost' => null,
                'completed_at' => null,
                'created_at' => '2024-03-20 14:45:00',
                'updated_at' => '2024-03-20 14:45:00',
            ],
            [
                'tenant_id' => 3, // Patrick Biya Jr
                'property_id' => 3, // Studios Bastos Premium
                'title' => 'Problème électrique - prises ne marchent pas',
                'description' => 'Toutes les prises électriques de la cuisine ont cessé de fonctionner ce matin. Le disjoncteur général fonctionne normalement.',
                'category' => 'electrical',
                'priority' => 'urgent',
                'status' => 'open',
                'assigned_to' => null,
                'images' => null,
                'estimated_cost' => null,
                'actual_cost' => null,
                'completed_at' => null,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'tenant_id' => 1, // Jean Fotso
                'property_id' => 1, // Villa Moderne Bonanjo
                'title' => 'Nettoyage professionnel demandé',
                'description' => 'Demande de nettoyage professionnel des vitres et du jardin avant la visite de la famille.',
                'category' => 'cleaning',
                'priority' => 'low',
                'status' => 'completed',
                'assigned_to' => 2, // Pierre Mbarga
                'images' => null,
                'estimated_cost' => 15000.00,
                'actual_cost' => 15000.00,
                'completed_at' => '2024-02-28 16:00:00',
                'created_at' => '2024-02-25 10:30:00',
                'updated_at' => '2024-02-28 16:00:00',
            ],
        ];

        DB::table('maintenance_requests')->insert($maintenanceRequests);
    }
}
