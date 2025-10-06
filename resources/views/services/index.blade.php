@extends('layouts.main')

@section('title', 'Professional Services - TeamO Digitals')
@section('description', 'Comprehensive digital services including records digitization, web development, IT support, and business consulting. Transform your business with our expert solutions and drive sustainable growth.')
@section('keywords', 'digital services, records digitization, web development, IT support, business consulting, digital transformation, TeamO Digitals services')
@section('og_title', 'Professional Digital Services - Transform Your Business Today')
@section('og_description', 'Expert digital transformation services including records digitization, web development, IT support, and business consulting to drive your business growth.')
@section('og_type', 'website')

@push('structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Service",
    "provider": {
        "@type": "Organization",
        "name": "TeamO Digitals"
    },
    "serviceType": "Digital Transformation Services",
    "description": "Comprehensive digital services including records digitization, web development, IT support, and business consulting",
    "offers": [
        @foreach($services as $service)
        {
            "@type": "Offer",
            "name": "{{ $service['title'] }}",
            "description": "{{ $service['description'] }}",
            "price": "{{ $service['price'] }}",
            "category": "{{ ucfirst($service['category']) }}",
            "url": "{{ route('services.show', $service['slug']) }}"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/services.css') }}">
@endpush

@section('content')
<!-- Services Hero Section -->
<section class="services-hero">
    <div class="services-hero-content">
        <div class="container">
            <h1 class="animate-fade-in-up">Our Professional Services</h1>
            <p class="animate-fade-in-up">Comprehensive digital solutions tailored to transform your business operations and drive sustainable growth in the modern marketplace.</p>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section">
    <div class="container">
        <div class="services-container">
            @foreach($services as $service)
            <div class="service-card animate-fade-in-up">
                <div class="service-image">
                    <img src="{{ asset($service['image']) }}" alt="{{ $service['title'] }}">
                    <div class="service-image-overlay">
                        <div class="service-icon">{{ $service['icon'] }}</div>
                    </div>
                </div>
                
                <div class="service-content">
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['short_description'] }}</p>
                    
                    <ul class="service-features">
                        @foreach(array_slice($service['features'], 0, 3) as $feature)
                        <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="service-footer">
                    <div class="service-price">{{ $service['price'] }}</div>
                    <a href="{{ route('services.show', $service['slug']) }}" class="read-more-btn">Learn More</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-container">
            @foreach($stats as $stat)
            <div class="stat-item animate-fade-in-up">
                <div class="stat-number">{{ $stat['number'] }}</div>
                <div class="stat-label">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="process-section">
    <div class="container">
        <div class="process-header text-center">
            <h2>Our Work Process</h2>
            <p>A streamlined approach to delivering exceptional results for every project</p>
        </div>
        
        <div class="process-steps">
            <div class="process-step animate-fade-in-up">
                <div class="step-number">1</div>
                <h3 class="step-title">Consultation</h3>
                <p class="step-description">We begin with a comprehensive consultation to understand your specific needs and objectives.</p>
            </div>
            
            <div class="process-step animate-fade-in-up">
                <div class="step-number">2</div>
                <h3 class="step-title">Planning</h3>
                <p class="step-description">Our team develops a detailed project plan with clear milestones and deliverables.</p>
            </div>
            
            <div class="process-step animate-fade-in-up">
                <div class="step-number">3</div>
                <h3 class="step-title">Implementation</h3>
                <p class="step-description">We execute the project with precision, keeping you informed throughout the process.</p>
            </div>
            
            <div class="process-step animate-fade-in-up">
                <div class="step-number">4</div>
                <h3 class="step-title">Delivery</h3>
                <p class="step-description">Final delivery with comprehensive testing, documentation, and ongoing support.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Transform Your Business?</h2>
            <p>Let's discuss how our services can help you achieve your digital transformation goals and drive sustainable growth.</p>
            
            <div class="cta-buttons">
                <a href="{{ route('contact') }}" class="cta-btn-primary">Get Started Today</a>
                <a href="{{ route('gallery') }}" class="cta-btn-secondary">View Our Work</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Add scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all animated elements
    document.querySelectorAll('.animate-fade-in-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });

    // Add hover effects to service cards
    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>
@endpush
