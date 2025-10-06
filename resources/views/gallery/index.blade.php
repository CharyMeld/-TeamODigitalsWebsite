@extends('layouts.main')

@section('title', 'Project Gallery - TeamO Digitals')
@section('description', 'Explore our comprehensive portfolio of successful digital transformation projects including business consulting, data digitization, software development, and IT support solutions.')
@section('keywords', 'project gallery, portfolio, business consulting projects, data digitization examples, software development portfolio, IT support cases, TeamO Digitals work')
@section('og_title', 'Project Gallery - See Our Successful Digital Transformation Projects')
@section('og_description', 'Browse through our extensive portfolio showcasing successful business consulting, digitization, software development, and IT support projects.')
@section('og_type', 'website')

@push('structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "Project Gallery",
    "description": "Portfolio of successful digital transformation projects by TeamO Digitals",
    "url": "{{ url()->current() }}",
    "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": {{ count($images) }},
        "itemListElement": [
            @foreach($images as $index => $image)
            {
                "@type": "CreativeWork",
                "position": {{ $index + 1 }},
                "name": "{{ $image['title'] }}",
                "description": "{{ $image['description'] }}",
                "image": "{{ asset($image['path']) }}",
                "genre": "{{ ucfirst($image['category']) }}"
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/gallery.css') }}">
@endpush

@section('content')
<!-- Gallery Hero Section -->
<section class="gallery-hero">
    <div class="gallery-hero-content">
        <div class="container">
            <h1 class="animate-fade-in-up">Our Project Gallery</h1>
            <p class="animate-fade-in-up">Explore our amazing portfolio of successful projects. Click on any image to view in full-screen mode and discover the quality of our work.</p>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="filter-section">
    <div class="container">
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">
                <span>All Projects</span>
            </button>
            <button class="filter-btn" data-filter="consulting">
                <span>Business Consulting</span>
            </button>
            <button class="filter-btn" data-filter="digitization">
                <span>Data Digitization</span>
            </button>
            <button class="filter-btn" data-filter="software">
                <span>Software Development</span>
            </button>
            <button class="filter-btn" data-filter="it-support">
                <span>IT Support</span>
            </button>
        </div>

        <!-- Gallery Grid -->
        <div class="gallery-grid" id="galleryGrid">
            @foreach($images as $image)
            <div class="gallery-item animate-fade-in-up" data-category="{{ $image['category'] }}">
                <img src="{{ asset($image['path']) }}" alt="{{ $image['title'] }}" onclick="openLightbox(this, '{{ $image['title'] }}', '{{ $image['description'] }}')">
                <div class="gallery-caption">
                    <h3>{{ $image['title'] }}</h3>
                    <p>{{ $image['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Animated Showcase -->
<section class="animated-showcase">
    <div class="container">
        <div class="showcase-header">
            <h2 class="animated-title">Featured Project Showcase</h2>
            <p class="animated-description">See some of our best work, elegantly showcased with detailed insights into our expertise and capabilities.</p>
        </div>
        
        <div class="showcase-slider" id="showcaseSlider">
            @foreach($showcaseProjects as $index => $project)
            <div class="slide {{ $index === 0 ? 'active' : '' }}">
                <img src="{{ asset($project['image']) }}" alt="{{ $project['title'] }}">
                <div class="slide-overlay">
                    <h3>{{ $project['title'] }}</h3>
                    <p>{{ $project['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox">
    <span class="close-lightbox" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img">
    <div id="lightbox-caption" class="lightbox-caption"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Filter functionality
    const filterButtons = document.querySelectorAll(".filter-btn");
    const galleryItems = document.querySelectorAll(".gallery-item");

    filterButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            // Remove active class from all buttons
            document.querySelector(".filter-btn.active").classList.remove("active");
            btn.classList.add("active");

            const filter = btn.getAttribute("data-filter");
            
            // Filter gallery items
            galleryItems.forEach(item => {
                if (filter === "all" || item.getAttribute("data-category") === filter) {
                    item.style.display = "block";
                    item.style.animation = "fadeInUp 0.6s ease-out";
                } else {
                    item.style.display = "none";
                }
            });
        });
    });

    // Showcase slider
    let currentSlide = 0;
    const slides = document.querySelectorAll('#showcaseSlider .slide');

    function showNextSlide() {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }

    // Auto-advance slides every 4 seconds
    setInterval(showNextSlide, 4000);
});

// Lightbox functionality
function openLightbox(img, title, description) {
    const lightbox = document.getElementById("lightbox");
    const lightboxImg = document.getElementById("lightbox-img");
    const lightboxCaption = document.getElementById("lightbox-caption");
    
    lightbox.classList.add("active");
    lightboxImg.src = img.src;
    lightboxCaption.innerHTML = `<h3>${title}</h3><p>${description}</p>`;
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById("lightbox");
    lightbox.classList.remove("active");
    
    // Restore body scroll
    document.body.style.overflow = 'auto';
}

// Close lightbox on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Close lightbox on background click
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLightbox();
    }
});
</script>
@endpush




