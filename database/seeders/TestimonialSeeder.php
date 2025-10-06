<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing clients
        $client1 = Client::where('name', 'Faculty of Economics and Management Sciences')->first();
        $client2 = Client::where('name', 'West African College of Physicians')->first();

        if ($client1) {
            Testimonial::create([
                'client_id' => $client1->id,
                'client_name' => 'Dr. Adebayo Ogundimu',
                'client_position' => 'Dean, Faculty of Economics',
                'testimonial' => 'The digital transformation solutions provided have revolutionized how we manage our academic programs and student records. The efficiency gains have been remarkable.',
                'client_image' => 'testimonial1.jpg',
                'status' => 'active',
                'sort_order' => 1,
                'rating' => 5.0,
            ]);
        }

        if ($client2) {
            Testimonial::create([
                'client_id' => $client2->id,
                'client_name' => 'Prof. Kemi Odukoya',
                'client_position' => 'President, WACP',
                'testimonial' => 'Their IT consulting expertise helped us streamline our certification processes across West Africa. Outstanding service and support throughout the implementation.',
                'client_image' => 'testimonial2.jpg',
                'status' => 'active',
                'sort_order' => 2,
                'rating' => 5.0,
            ]);
        }

        // Add a few more sample testimonials
        if ($client1) {
            Testimonial::create([
                'client_id' => $client1->id,
                'client_name' => 'Mrs. Folake Adeniyi',
                'client_position' => 'Head of IT Services',
                'testimonial' => 'The software development team delivered exactly what we needed. The custom solutions have improved our workflow significantly.',
                'client_image' => 'testimonial3.jpg',
                'status' => 'active',
                'sort_order' => 3,
                'rating' => 4.8,
            ]);
        }
    }
}
