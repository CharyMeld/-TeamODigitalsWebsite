<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Faculty of Economics and Management Sciences',
                'logo' => 'client1.jpg',
                'description' => 'The Faculty of Economics and Management Sciences was created by the Department of Economics to cater for the demands of stakeholders who apparently are in favour of the departure from the traditional economics education and training in Ibadan, which used to be a highly concentrated one encompassing all aspects of management related areas of economics, business administration, banking and finance, and accountancy as well as marketing.',
                'website' => 'https://econs.ui.edu.ng/',
            ],
            [
                'name' => 'West African College of Physicians',
                'logo' => 'client2.jpg',
                'description' => 'Since 1976, WACP have been responsible for postgraduate specialist training of doctors in the five Anglophone West African countries, to increase equity and achievement for all candidates.',
                'website' => 'https://wacpcoam.org/',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
