<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first admin or superadmin user
        $author = User::whereIn('role', ['admin', 'superadmin', 'developer'])->first();
        
        if (!$author) {
            $this->command->error('No admin/superadmin user found. Please create one first.');
            return;
        }

        $blogs = [
            [
                'title' => 'Transform Your Business with Records Digitalisation: A Complete Guide',
                'slug' => 'transform-business-records-digitalisation-guide',
                'meta_description' => 'Discover how records digitalisation can revolutionize your business operations. Learn about document scanning, indexing, secure storage, and the benefits of going paperless.',
                'primary_keyword' => 'records digitalisation',
                'secondary_keywords' => json_encode(['document scanning', 'digital transformation', 'paperless office', 'document management', 'data migration']),
                'introduction' => 'In today\'s fast-paced business environment, managing physical documents can be time-consuming, costly, and inefficient. Records digitalisation offers a transformative solution, converting paper documents into organized, searchable digital files. This comprehensive guide explores how your business can benefit from professional document digitalisation services.',
                'sections' => json_encode([
                    [
                        'title' => 'What is Records Digitalisation?',
                        'content' => 'Records digitalisation is the process of converting physical documents, files, and records into digital formats. This transformation involves high-quality scanning, intelligent indexing, and secure digital storage solutions. Unlike simple scanning, professional digitalisation includes comprehensive data organization, making your documents easily searchable and accessible.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Key Benefits of Going Digital',
                        'content' => 'The advantages of records digitalisation extend far beyond just saving physical space. Businesses experience improved efficiency through instant document retrieval, enhanced security with encrypted digital storage, cost savings from reduced paper and printing expenses, and better compliance with data protection regulations. Digital records also enable remote access, supporting modern hybrid work environments.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => [
                            [
                                'title' => 'Improved Accessibility',
                                'content' => 'Digital records can be accessed from anywhere, anytime, by authorized personnel. This eliminates the need to physically search through filing cabinets and enables instant information retrieval.'
                            ],
                            [
                                'title' => 'Enhanced Security',
                                'content' => 'Digital documents can be encrypted, backed up automatically, and protected with access controls. This provides better security than physical documents stored in filing cabinets.'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Our Digitalisation Process',
                        'content' => 'Our on-site digitalisation service ensures maximum security for your sensitive documents. We begin with a thorough assessment of your records, followed by systematic scanning using high-resolution equipment. Each document is then indexed with relevant metadata, categorized appropriately, and securely stored in your chosen digital management system. Your original documents are returned organized and labeled for easy reference.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Industries We Serve',
                        'content' => 'Our records digitalisation services benefit various sectors including healthcare facilities managing patient records, legal firms handling case files, educational institutions archiving student records, government agencies maintaining public documents, and corporate offices streamlining administrative paperwork. Each industry has unique requirements, and we tailor our approach accordingly.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ]
                ]),
                'conclusion' => 'Records digitalisation is not just about converting paper to digital format—it\'s about transforming how your business manages information. With professional digitalisation services, you gain efficiency, security, and accessibility while reducing costs and environmental impact. Ready to make the digital transformation? Contact Teamo Digital Solutions today to discuss your records digitalisation needs.',
                'cta_text' => 'Start Your Digital Transformation',
                'cta_link' => '/contact',
                'featured_image' => 'storage/blogs/records-digitalisation.jpg',
                'featured_image_alt' => 'Professional records digitalisation service scanning documents',
                'tags' => json_encode(['Digitalisation', 'Document Management', 'Business Efficiency', 'Digital Transformation']),
                'canonical_url' => '/blog/transform-business-records-digitalisation-guide',
                'reading_time' => 8,
            ],
            [
                'title' => 'Custom Software Development: Why Off-the-Shelf Solutions Don\'t Always Work',
                'slug' => 'custom-software-development-vs-off-shelf-solutions',
                'meta_description' => 'Learn why custom software development delivers better ROI than generic solutions. Explore tailored database systems, document management, and business-specific applications.',
                'primary_keyword' => 'custom software development',
                'secondary_keywords' => json_encode(['bespoke software', 'database management systems', 'document management software', 'business automation', 'custom applications']),
                'introduction' => 'Every business is unique, with specific workflows, requirements, and challenges. While off-the-shelf software may seem cost-effective initially, it often forces businesses to adapt their processes to fit the software—rather than the other way around. Custom software development offers a superior alternative, creating solutions perfectly tailored to your business needs.',
                'sections' => json_encode([
                    [
                        'title' => 'The Limitations of Generic Software',
                        'content' => 'Off-the-shelf software is designed for mass appeal, which means it includes numerous features most businesses never use while lacking specific functionality they actually need. This leads to inefficient workflows, workarounds, and frustrated employees. Generic software also requires your team to learn systems that don\'t match how they naturally work, resulting in lower productivity and resistance to adoption.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Benefits of Custom Development',
                        'content' => 'Custom software is built around your exact requirements. It integrates seamlessly with your existing systems, scales as your business grows, and includes only the features you need—eliminating clutter and confusion. The result is software that your team actually wants to use, leading to faster adoption, improved efficiency, and better return on investment.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => [
                            [
                                'title' => 'Perfect Fit for Your Workflow',
                                'content' => 'Custom software mirrors your business processes exactly, eliminating the need for workarounds or process changes to accommodate generic software limitations.'
                            ],
                            [
                                'title' => 'Competitive Advantage',
                                'content' => 'While competitors struggle with generic tools, your custom solution gives you unique capabilities that set you apart in the market.'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Our Development Approach',
                        'content' => 'We begin every project with in-depth consultation to understand your business operations, pain points, and objectives. Our agile development methodology ensures you\'re involved throughout the process, with regular previews and opportunities for feedback. We specialize in database management systems that organize your information intelligently and document management software that streamlines your file handling processes.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Long-term Value and Support',
                        'content' => 'Unlike subscription-based software with ongoing fees, custom solutions provide lasting value. You own the software outright, and we provide continued support to ensure it evolves with your business. As your needs change, your software can be updated and enhanced without the constraints of vendor-controlled updates.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ]
                ]),
                'conclusion' => 'Custom software development is an investment in your business\'s efficiency and competitive advantage. While the initial cost may be higher than off-the-shelf alternatives, the long-term benefits—increased productivity, perfect workflow alignment, and scalability—deliver superior return on investment. Let Teamo Digital Solutions create software that works exactly how your business needs it to.',
                'cta_text' => 'Discuss Your Custom Software Needs',
                'cta_link' => '/contact',
                'featured_image' => 'storage/blogs/custom-software-development.jpg',
                'featured_image_alt' => 'Custom software development team coding business solutions',
                'tags' => json_encode(['Software Development', 'Custom Solutions', 'Business Technology', 'Digital Innovation']),
                'canonical_url' => '/blog/custom-software-development-vs-off-shelf-solutions',
                'reading_time' => 7,
            ],
            [
                'title' => 'IT Support Services: Keeping Your Business Technology Running Smoothly',
                'slug' => 'it-support-services-business-technology-guide',
                'meta_description' => 'Comprehensive IT support services including troubleshooting, system maintenance, networking, cybersecurity, and equipment sales. Keep your business technology reliable and secure.',
                'primary_keyword' => 'IT support services',
                'secondary_keywords' => json_encode(['technical support', 'network management', 'cybersecurity', 'system maintenance', 'IT equipment']),
                'introduction' => 'Technology drives modern business, but when systems fail or security is compromised, productivity grinds to a halt. Professional IT support services ensure your technology infrastructure remains reliable, secure, and optimized for performance. From daily troubleshooting to strategic cybersecurity, comprehensive IT support is essential for business continuity.',
                'sections' => json_encode([
                    [
                        'title' => 'Proactive System Maintenance',
                        'content' => 'Prevention is better than cure, especially in IT. Our proactive maintenance approach includes regular system health checks, timely software updates, performance optimization, and hardware monitoring. This preventive strategy identifies and resolves potential issues before they cause downtime, keeping your business running smoothly without unexpected interruptions.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Rapid Troubleshooting and Problem Resolution',
                        'content' => 'When issues arise, speed matters. Our experienced technicians quickly diagnose and resolve computer problems, software glitches, and network disruptions. With comprehensive diagnostic tools and deep technical expertise, we minimize downtime and get your team back to work fast. Our support covers hardware failures, software conflicts, connectivity issues, and performance problems.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => [
                            [
                                'title' => 'Remote and On-Site Support',
                                'content' => 'Many issues can be resolved remotely, providing instant assistance. For complex problems requiring hands-on intervention, our technicians are ready for on-site support.'
                            ],
                            [
                                'title' => '24/7 Emergency Response',
                                'content' => 'Critical systems don\'t fail on schedule. Our emergency support ensures you have access to expert help whenever technology problems threaten your operations.'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Network Infrastructure Management',
                        'content' => 'A reliable network is your business\'s digital backbone. We design, implement, and manage robust networking solutions including Wi-Fi setup, ethernet infrastructure, router configuration, and bandwidth optimization. Our network management ensures stable connectivity, optimal performance, and seamless communication across your organization.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Comprehensive Cybersecurity Protection',
                        'content' => 'Cyber threats evolve constantly, requiring vigilant protection. Our cybersecurity services include firewall implementation, antivirus deployment, data encryption, security audits, and staff training. We create multiple layers of defense to protect your sensitive information from ransomware, phishing, malware, and unauthorized access. Regular security assessments ensure your defenses stay current against emerging threats.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'IT Equipment Sales and Repairs',
                        'content' => 'Beyond support services, we provide trusted IT equipment sourcing and professional repair services. Whether you need new computers, servers, networking equipment, or repairs for existing hardware, we ensure you get reliable technology at competitive prices. Our repair services restore malfunctioning equipment quickly, often at a fraction of replacement cost.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ]
                ]),
                'conclusion' => 'Effective IT support is not just about fixing problems—it\'s about creating a reliable, secure, and efficient technology environment that empowers your business. With Teamo Digital Solutions\' comprehensive IT support services, you gain peace of mind knowing your technology infrastructure is professionally managed, proactively maintained, and protected against threats. Focus on your business while we keep your technology running perfectly.',
                'cta_text' => 'Get Reliable IT Support Today',
                'cta_link' => '/contact',
                'featured_image' => 'storage/blogs/it-support-services.jpg',
                'featured_image_alt' => 'IT support technician providing computer maintenance services',
                'tags' => json_encode(['IT Support', 'Cybersecurity', 'Network Management', 'Technical Support']),
                'canonical_url' => '/blog/it-support-services-business-technology-guide',
                'reading_time' => 9,
            ],
            [
                'title' => 'Business Consultancy Services: Strategic Technology Guidance for Growth',
                'slug' => 'business-consultancy-services-technology-strategy',
                'meta_description' => 'Expert IT consultancy services including infrastructure audits, process optimization, data security consulting, and strategic IT planning. Make confident technology decisions.',
                'primary_keyword' => 'business consultancy services',
                'secondary_keywords' => json_encode(['IT consulting', 'digital transformation', 'process optimization', 'data security', 'strategic planning']),
                'introduction' => 'Technology should enable business growth, not create obstacles. Yet many organizations struggle with inefficient systems, security vulnerabilities, and unclear digital strategies. Professional consultancy services provide the expert guidance needed to transform technology from a challenge into a competitive advantage. Our approach combines technical expertise with business understanding to deliver practical, effective solutions.',
                'sections' => json_encode([
                    [
                        'title' => 'Digital Infrastructure Audits',
                        'content' => 'Understanding your current technology landscape is the first step toward improvement. Our comprehensive infrastructure audits examine every aspect of your IT systems—hardware, software, networks, security measures, and data management practices. We identify inefficiencies, vulnerabilities, and opportunities for enhancement. The result is a detailed report with prioritized recommendations and a clear roadmap for stronger, more efficient technology use.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Process Optimization and Workflow Analysis',
                        'content' => 'Inefficient processes waste time, create errors, and frustrate employees. We analyze how work flows through your organization, identifying bottlenecks, redundancies, and opportunities for automation. Our recommendations focus on practical improvements that reduce delays, minimize errors, and improve overall efficiency. Whether it\'s streamlining document approval processes, automating repetitive tasks, or improving inter-department communication, we help you work smarter.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => [
                            [
                                'title' => 'Workflow Mapping',
                                'content' => 'We document your current processes visually, making it easy to see where improvements can be made and how technology can enhance efficiency.'
                            ],
                            [
                                'title' => 'Automation Opportunities',
                                'content' => 'Identify tasks that can be automated, freeing your team to focus on high-value activities that require human judgment and creativity.'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Data Security and Compliance Consulting',
                        'content' => 'Data breaches and compliance failures can devastate businesses. Our security consultants evaluate your current data protection measures, identify vulnerabilities, and recommend comprehensive solutions. We help you build a secure environment that meets regulatory requirements while protecting your sensitive information. Our guidance covers access controls, encryption strategies, backup procedures, incident response planning, and staff security training.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Strategic IT Planning for Business Growth',
                        'content' => 'Technology decisions should align with business objectives. We help you create forward-looking IT strategies that support your growth plans. Whether you\'re expanding to new locations, launching new products, or improving operational efficiency, we ensure your technology roadmap matches your business vision. Our strategic planning considers budget constraints, scalability requirements, industry trends, and competitive advantages.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ],
                    [
                        'title' => 'Vendor Selection and Technology Assessment',
                        'content' => 'Choosing the right technology solutions and vendors can be overwhelming. We provide objective assessments of available options, helping you make informed decisions that fit your needs and budget. Our vendor-neutral approach ensures recommendations are based solely on what\'s best for your business, not sales commissions or partnerships.',
                        'images' => [],
                        'alt_text' => '',
                        'subsections' => []
                    ]
                ]),
                'conclusion' => 'Effective consultancy transforms uncertainty into clarity and challenges into opportunities. With Teamo Digital Solutions\' consultancy services, you gain expert guidance shaped around your unique needs, resources, and goals. We don\'t just identify problems—we provide practical solutions and ongoing support to ensure successful implementation. Make confident technology decisions that strengthen your operations and drive business growth.',
                'cta_text' => 'Schedule a Consultation',
                'cta_link' => '/contact',
                'featured_image' => 'storage/blogs/consultancy-services.jpg',
                'featured_image_alt' => 'Business consultancy meeting discussing IT strategy',
                'tags' => json_encode(['Consultancy', 'IT Strategy', 'Business Growth', 'Digital Transformation']),
                'canonical_url' => '/blog/business-consultancy-services-technology-strategy',
                'reading_time' => 8,
            ],
        ];

        foreach ($blogs as $blogData) {
            // Calculate reading time based on word count
            $totalWords = str_word_count($blogData['introduction']);
            $totalWords += str_word_count($blogData['conclusion']);
            
            $sections = json_decode($blogData['sections'], true);
            foreach ($sections as $section) {
                $totalWords += str_word_count($section['content']);
                if (isset($section['subsections'])) {
                    foreach ($section['subsections'] as $subsection) {
                        $totalWords += str_word_count($subsection['content']);
                    }
                }
            }
            
            $blogData['reading_time'] = ceil($totalWords / 200);
            
            // Generate schema markup
            $blogData['schema_markup'] = json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $blogData['title'],
                'description' => $blogData['meta_description'],
                'author' => [
                    '@type' => 'Person',
                    'name' => $author->name,
                ],
                'datePublished' => now()->toIso8601String(),
                'dateModified' => now()->toIso8601String(),
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Teamo Digital Solutions',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => url('/images/logo.png'),
                    ],
                ],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => url($blogData['canonical_url']),
                ],
                'keywords' => $blogData['primary_keyword'],
            ]);

            // Generate content from introduction + sections + conclusion for backward compatibility
            $content = $blogData['introduction'] . "\n\n";
            $sections = json_decode($blogData['sections'], true);
            foreach ($sections as $section) {
                $content .= "## " . $section['title'] . "\n\n";
                $content .= $section['content'] . "\n\n";
                if (isset($section['subsections'])) {
                    foreach ($section['subsections'] as $subsection) {
                        $content .= "### " . $subsection['title'] . "\n\n";
                        $content .= $subsection['content'] . "\n\n";
                    }
                }
            }
            $content .= $blogData['conclusion'];

            Blog::create([
                'author_id' => $author->id,
                'title' => $blogData['title'],
                'slug' => $blogData['slug'],
                'content' => $content, // Add content field for backward compatibility
                'excerpt' => substr($blogData['meta_description'], 0, 200),
                'meta_description' => $blogData['meta_description'],
                'primary_keyword' => $blogData['primary_keyword'],
                'secondary_keywords' => $blogData['secondary_keywords'],
                'introduction' => $blogData['introduction'],
                'sections' => $blogData['sections'],
                'conclusion' => $blogData['conclusion'],
                'cta_text' => $blogData['cta_text'],
                'cta_link' => $blogData['cta_link'],
                'featured_image' => $blogData['featured_image'],
                'featured_image_alt' => $blogData['featured_image_alt'],
                'tags' => $blogData['tags'],
                'canonical_url' => $blogData['canonical_url'],
                'schema_markup' => $blogData['schema_markup'],
                'reading_time' => $blogData['reading_time'],
                'status' => 'published',
                'published_at' => now(),
                'views' => rand(50, 500),
                'likes' => rand(5, 50),
            ]);
        }

        $this->command->info('Successfully created ' . count($blogs) . ' SEO-optimized blog posts!');
    }
}
