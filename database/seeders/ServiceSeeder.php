<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing services
        Service::truncate();

        $services = [
            [
                'title' => 'Records Digitalisation',
                'slug' => 'records-digitalisation',
                'description' => 'Convert paper documents into digital files that are organised, easy to find, and safe to store. Features: Document scanning (bulk and single item), indexing and categorisation of digital records, secure management and archiving of digital files, data migration and storage solutions. What you get: Less paperwork and faster access to important records, a searchable database with documents tagged and named properly, physical documents returned, labelled, and neatly reorganised. Note: Service is carried out on-site to ensure document security.',
                'image' => 'images/services/records_digitalisation.png', // Changed to images/
                'icon' => 'fa-file-alt', // Added appropriate icon
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Custom Software Development',
                'slug' => 'custom-software-development',
                'description' => 'Our experts create software that fits the way your business works, helping you manage information and tasks more easily. Features: Database management systems, document management software. What you get: Tools designed for your specific needs, not off-the-shelf solutions, faster access to information when you need it, less time on manual filing and searching, a simpler way to keep records organised and secure.',
                'image' => 'images/services/custom_software_development.png', // Changed to images/
                'icon' => 'fa-code', // Added appropriate icon
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'IT Support Services',
                'slug' => 'it-support-services',
                'description' => 'Keep your technology working reliably and securely with expert support. Troubleshooting & Diagnostics: Get quick fixes for computer, software, or network issues so work is not interrupted. System Maintenance & Updates: Keep systems running smoothly with regular checks, updates, and performance improvements. Networking Support: Set up and manage internet, Wi-Fi, and office connections to stay connected without disruptions. Cybersecurity: Protect sensitive information with firewalls, antivirus, and encryption tools. Sales & Repairs: Access trusted IT equipment and dependable repairs when you need them.',
                'image' => 'images/services/it_support_services.png', // Changed to images/
                'icon' => 'fa-tools', // Added appropriate icon
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Consultancy Services',
                'slug' => 'consultancy-services',
                'description' => 'When technology creates confusion instead of clarity, businesses need more than quick fixes. They need guidance. Our consultancy services identify the root of digital and operational challenges and provide clear, practical solutions tailored to your business goals. Digital Infrastructure Audits: We examine your current IT systems, pinpoint weak spots, and provide a roadmap for stronger, more efficient technology use. Process Optimisation: We study how work flows across your teams and recommend adjustments that cut delays, reduce errors, and improve overall efficiency. Data Security Consulting: We evaluate your data protection measures, uncover risks, and guide you on how to build a secure and compliant environment. Strategic IT Planning: We help you create a forward-looking IT strategy that aligns with your business vision, so your technology grows with you instead of against you. We provide expert advice shaped around your needs, resources, and goals. Our role is to help you make confident decisions that strengthen your operations.',
                'image' => 'images/services/consultancy_services.png', // Changed to images/
                'icon' => 'fa-user-tie', // Added appropriate icon
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
