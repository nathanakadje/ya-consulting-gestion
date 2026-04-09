<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name'           => 'Ministère des Infrastructures',
                'email'          => 'contact@infrastructures.gouv.ci',
                'phone'          => '+225 27 20 21 00 00',
                'contact_person' => 'M. Bamba Coulibaly',
                'city'           => 'Abidjan',
            ],
            [
                'name'           => 'SODECI',
                'email'          => 'dg@sodeci.ci',
                'phone'          => '+225 27 20 25 25 25',
                'contact_person' => 'Mme Adjoua Konan',
                'city'           => 'Abidjan',
            ],
            [
                'name'           => 'Orange Côte d\'Ivoire',
                'email'          => 'procurement@orange.ci',
                'phone'          => '+225 07 00 00 00 00',
                'contact_person' => 'M. Diallo Ibrahim',
                'city'           => 'Abidjan',
            ],
            [
                'name'           => 'Groupe NSIA',
                'email'          => 'info@groupensia.com',
                'phone'          => '+225 27 20 31 90 00',
                'contact_person' => 'Mme Traoré Fatoumata',
                'city'           => 'Abidjan',
            ],
            [
                'name'           => 'RTI',
                'email'          => 'dg@rti.ci',
                'phone'          => '+225 27 22 48 01 01',
                'contact_person' => 'M. Koné Mamadou',
                'city'           => 'Abidjan',
            ],
        ];

        foreach ($clients as $client) {
            Client::create(array_merge($client, ['country' => 'Côte d\'Ivoire']));
        }
    }
}
