<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index()
    {
        $services = [
            [
                'id' => 'records-digitalisation',
                'title' => 'Records Digitalisation',
                'intro' => 'Convert paper documents into digital files that are organised, easy to find, and safe to store.',
                'features' => [
                    'Document scanning (bulk and single item).',
                    'Indexing and categorisation of digital records.',
                    'Secure management and archiving of digital files.',
                    'Data migration and storage solutions.'
                ],
                'benefits' => [
                    'Less paperwork and faster access to important records.',
                    'A searchable database with documents tagged and named properly.',
                    'Physical documents returned, labelled, and neatly reorganised.'
                ],
                'note' => 'Service is carried out on-site to ensure document security.',
                'cta_text' => "Let's Secure Your Records",
                'cta_subtext' => 'Book a consultation with our experts.',
                'icon' => 'fas fa-file-alt',
                'image' => 'images/services/records_digitalisation.png',
                'slug' => 'records-digitalisation'
            ],
            [
                'id' => 'custom-software',
                'title' => 'Custom Software Development',
                'intro' => 'Our experts create software that fits the way your business works, helping you manage information and tasks more easily.',
                'features' => [
                    'Database management systems.',
                    'Document management software.'
                ],
                'benefits' => [
                    'Tools designed for your specific needs, not off-the-shelf solutions.',
                    'Faster access to information when you need it.',
                    'Less time on manual filing and searching.',
                    'A simpler way to keep records organised and secure.'
                ],
                'note' => null,
                'cta_text' => "Let's Build Your Solution",
                'cta_subtext' => 'Talk to our development team.',
                'icon' => 'fas fa-code',
                'image' => 'images/services/custom_software_development.png',
                'slug' => 'custom-software-development'
            ],
            [
                'id' => 'it-support',
                'title' => 'IT Support Services',
                'intro' => 'Keep your technology working reliably and securely with expert support.',
                'services' => [
                    [
                        'name' => 'Troubleshooting & Diagnostics',
                        'description' => 'Get quick fixes for computer, software, or network issues so work is not interrupted.'
                    ],
                    [
                        'name' => 'System Maintenance & Updates',
                        'description' => 'Keep systems running smoothly with regular checks, updates, and performance improvements.'
                    ],
                    [
                        'name' => 'Networking Support',
                        'description' => 'Set up and manage internet, Wi-Fi, and office connections to stay connected without disruptions.'
                    ],
                    [
                        'name' => 'Cybersecurity',
                        'description' => 'Protect sensitive information with firewalls, antivirus, and encryption tools.'
                    ],
                    [
                        'name' => 'Sales & Repairs',
                        'description' => 'Access trusted IT equipment and dependable repairs when you need them.'
                    ]
                ],
                'cta_text' => 'Talk to an Expert',
                'cta_subtext' => 'Get reliable IT support today.',
                'icon' => 'fas fa-headset',
                'image' => 'images/services/it_support_services.png',
                'slug' => 'it-support-services'
            ],
            [
                'id' => 'consultancy',
                'title' => 'Consultancy Services',
                'intro' => 'When technology creates confusion instead of clarity, businesses need more than quick fixes. They need guidance. Our consultancy services identify the root of digital and operational challenges and provide clear, practical solutions tailored to your business goals.',
                'services' => [
                    [
                        'name' => 'Digital Infrastructure Audits',
                        'description' => 'We examine your current IT systems, pinpoint weak spots, and provide a roadmap for stronger, more efficient technology use.'
                    ],
                    [
                        'name' => 'Process Optimisation',
                        'description' => 'We study how work flows across your teams and recommend adjustments that cut delays, reduce errors, and improve overall efficiency.'
                    ],
                    [
                        'name' => 'Data Security Consulting',
                        'description' => 'We evaluate your data protection measures, uncover risks, and guide you on how to build a secure and compliant environment.'
                    ],
                    [
                        'name' => 'Strategic IT Planning',
                        'description' => 'We help you create a forward-looking IT strategy that aligns with your business vision, so your technology grows with you instead of against you.'
                    ]
                ],
                'footer' => 'We provide expert advice shaped around your needs, resources, and goals. Our role is to help you make confident decisions that strengthen your operations.',
                'cta_text' => 'Get Expert Guidance',
                'cta_subtext' => 'Schedule a consultation.',
                'icon' => 'fas fa-lightbulb',
                'image' => 'images/services/consultancy_services.png',
                'slug' => 'consultancy-services'
            ]
        ];

        return view('services', [
            'services' => $services,
            'page_title' => 'Our Services - TeamO Digitals',
            'meta_description' => 'Explore TeamO Digitals comprehensive digital services including web development, IT support, records digitization, and business consultancy.',
            'canonical_url' => url('/services'),
            'og_title' => 'Digital Services by TeamO Digitals - Web Development & IT Solutions',
            'og_description' => 'Professional digital services including web development, IT support, records digitization, and consultancy services in Nigeria.',
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'TeamO Digitals',
                    'url' => url('/'),
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressCountry' => 'Nigeria'
                    ]
                ],
                'serviceType' => [
                    'Web Development',
                    'IT Support',
                    'Records Digitization',
                    'Business Consultancy'
                ]
            ]
        ]);
    }

    public function show($slug)
    {
        $serviceDetails = $this->getServiceDetails($slug);
        
        if (!$serviceDetails) {
            abort(404);
        }

        return view('service-detail', [
            'service' => $serviceDetails,
            'page_title' => $serviceDetails['title'] . ' - TeamO Digitals',
            'meta_description' => $serviceDetails['meta_description'],
            'canonical_url' => url('/services/' . $slug),
            'og_title' => $serviceDetails['title'] . ' Services by TeamO Digitals',
            'og_description' => $serviceDetails['meta_description']
        ]);
    }

    private function getServiceDetails($slug)
    {
        $services = [
            'records-digitization-digitalization' => [
                'title' => 'Records Digitization and Digitalization',
                'description' => 'Transform your physical documents into secure, accessible digital formats.',
                'meta_description' => 'Professional records digitization services. Convert physical documents to digital formats for improved accessibility and security.',
                'content' => 'Our comprehensive digitization services help organizations transition from paper-based systems to efficient digital workflows.',
                'features' => [
                    'Document Scanning & OCR',
                    'Data Entry & Validation',
                    'Digital Archive Management',
                    'Security & Backup Solutions'
                ]
            ],
            'web-development' => [
                'title' => 'Web Development',
                'description' => 'Custom web solutions built with modern technologies.',
                'meta_description' => 'Professional web development services. Custom websites, web applications, and e-commerce solutions built with latest technologies.',
                'content' => 'We create responsive, scalable websites that deliver exceptional user experiences and drive business growth.',
                'features' => [
                    'Responsive Design',
                    'E-commerce Solutions',
                    'Content Management Systems',
                    'Web Application Development'
                ]
            ],
            'it-support' => [
                'title' => 'IT Support',
                'description' => 'Comprehensive IT support and maintenance services.',
                'meta_description' => 'Reliable IT support services. Technical assistance, system maintenance, and IT infrastructure management for businesses.',
                'content' => 'Keep your business running smoothly with our comprehensive IT support and maintenance services.',
                'features' => [
                    '24/7 Technical Support',
                    'System Maintenance',
                    'Network Management',
                    'Data Backup & Recovery'
                ]
            ],
            'consultancy' => [
                'title' => 'Consultancy',
                'description' => 'Strategic digital transformation consulting.',
                'meta_description' => 'Expert digital consultancy services. Strategic advice for digital transformation and business process optimization.',
                'content' => 'Our expert consultants help businesses optimize their digital strategies and implement efficient technological solutions.',
                'features' => [
                    'Digital Strategy Planning',
                    'Process Optimization',
                    'Technology Assessment',
                    'Implementation Support'
                ]
            ]
        ];

        return $services[$slug] ?? null;
    }
}
