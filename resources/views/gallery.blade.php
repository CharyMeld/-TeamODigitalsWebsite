@extends('layouts.public')

@section('title', 'Project Gallery - TeamO Digitals')
@section('description', 'Explore our comprehensive portfolio of successful digital transformation projects including business consulting, data digitization, software development, and IT support solutions.')
@section('keywords', 'project gallery, portfolio, business consulting projects, data digitization examples, software development portfolio, IT support cases, TeamO Digitals work')
@section('og_title', 'Project Gallery - See Our Successful Digital Transformation Projects')
@section('og_description', 'Browse through our extensive portfolio showcasing successful business consulting, digitization, software development, and IT support projects.')
@section('og_type', 'website')
 
@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    "@type" => "CollectionPage",
    "name" => "Project Gallery",
    "description" => "Portfolio of successful digital transformation projects by TeamO Digitals",
    "url" => url()->current(),
    "mainEntity" => [
        "@type" => "ItemList",
        "numberOfItems" => count($images),
        "itemListElement" => collect($images)->values()->map(function($image, $index) {
            return [
                "@type" => "CreativeWork",
                "position" => $index + 1,
                "name" => basename($image['path']),
                "url" => url('storage/gallery/' . basename($image['path'])),
                "description" => $image['description'] ?? '',
            ];
        })->toArray()
    ]
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
</script>
@endpush

@push('styles')
<style>
    body {
        background-color: #ffffff;
        font-family: 'Inter', sans-serif;
    }

    .gallery-hero {
        background: #ffffff;
        padding: 80px 0;
        text-align: center;
        border-bottom: 2px solid #93c5fd;
        position: relative;
    }

    .gallery-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(147, 197, 253, 0.05);
    }

    .gallery-hero-content .container {
        position: relative;
        z-index: 1;
    }

    .gallery-hero h1 {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #000000;
    }

    .gallery-hero p {
        font-size: 1.2rem;
        color: #1e40af;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .filter-section {
        background: #ffffff;
        padding: 60px 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .filter-buttons {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 50px;
    }

    .filter-btn {
        background: #ffffff;
        border: none;
        color: #1e40af;
        padding: 12px 24px;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: #1e40af;
        color: #ffffff;
        border-color: #1e40af;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.2);
    }

    .gallery-grid {
        position: relative;
        max-width: 1200px;
        margin: 0 auto 60px;
        border-radius: 15px;
        overflow: visible;
        min-height: 600px;
    }

    .gallery-item {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        display: none;
        background: #ffffff;
        border-radius: 15px;
        overflow: hidden;
        border: none;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .gallery-item.active {
        display: block;
        opacity: 1;
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .gallery-item:hover {
        box-shadow: 0 20px 50px rgba(147, 197, 253, 0.3);
    }

    .gallery-item img {
        width: 100%;
        height: 500px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .gallery-item:hover img {
        transform: scale(1.05);
    }

    .gallery-caption {
        padding: 20px;
    }

    .gallery-caption h3 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: #000000;
    }

    .gallery-caption p {
        color: #1e40af;
        line-height: 1.5;
        font-size: 0.95rem;
    }

    /* Animated Showcase */
    .animated-showcase {
        background: #ffffff;
        padding: 80px 0;
        border-top: 1px solid #93c5fd;
    }

    .showcase-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .animated-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #000000;
        margin-bottom: 1rem;
    }

    .animated-description {
        font-size: 1.1rem;
        color: #1e40af;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .showcase-slider {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .slide {
        display: none;
        position: relative;
    }

    .slide.active {
        display: block;
    }

    .slide img {
        width: 100%;
        height: 600px;
        object-fit: cover;
    }

    .slide-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.95);
        padding: 30px;
        backdrop-filter: blur(10px);
        border-top: 1px solid #93c5fd;
    }

    .slide-overlay h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #000000;
    }

    .slide-overlay p {
        color: #1e40af;
        line-height: 1.6;
    }

    /* Lightbox */
    .lightbox {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        opacity: 0;
        visibility: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .lightbox.active {
        opacity: 1;
        visibility: visible;
    }

    .lightbox img {
        max-width: 90%;
        max-height: 80%;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .close-lightbox {
        position: absolute;
        top: 30px;
        right: 50px;
        color: #ffffff;
        font-size: 3rem;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .close-lightbox:hover {
        color: #93c5fd;
    }

    .lightbox-caption {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255, 255, 255, 0.95);
        padding: 20px 30px;
        border-radius: 10px;
        text-align: center;
        max-width: 500px;
        backdrop-filter: blur(10px);
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .lightbox-caption h3 {
        color: #000000;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .lightbox-caption p {
        color: #1e40af;
        margin: 0;
        line-height: 1.5;
    }

    /* Hero animations */
    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Gallery navigation arrows */
    .gallery-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.9);
        color: #1e40af;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        z-index: 10;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .gallery-nav:hover {
        background: #1e40af;
        color: white;
        transform: translateY(-50%) scale(1.1);
    }

    .gallery-nav-prev {
        left: 20px;
    }

    .gallery-nav-next {
        right: 20px;
    }

    /* Gallery indicators */
    .gallery-indicators {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }

    .gallery-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        border: 2px solid #1e40af;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .gallery-indicator.active {
        background: #1e40af;
        transform: scale(1.2);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .gallery-hero h1 {
            font-size: 2.5rem;
        }

        .gallery-hero p {
            font-size: 1rem;
        }

        .filter-buttons {
            justify-content: center;
        }

        .filter-btn {
            padding: 10px 18px;
            font-size: 14px;
        }

        .slide img {
            height: 400px;
        }

        .gallery-item img {
            height: 350px;
        }

        .gallery-grid {
            min-height: 450px;
        }

        .gallery-nav {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .gallery-nav-prev {
            left: 10px;
        }

        .gallery-nav-next {
            right: 10px;
        }

        .slide-overlay {
            padding: 20px;
        }

        .close-lightbox {
            top: 20px;
            right: 20px;
            font-size: 2rem;
        }

        .lightbox-caption {
            bottom: 20px;
            left: 20px;
            right: 20px;
            transform: none;
            max-width: none;
            padding: 15px 20px;
        }
    }

    @media (max-width: 480px) {
        .filter-buttons {
            flex-direction: column;
            align-items: center;
        }

        .filter-btn {
            width: 200px;
        }
    }
</style>
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
            <button class="filter-btn active" data-filter="all"><span>All Projects</span></button>
            <button class="filter-btn" data-filter="consulting"><span>Business Consulting</span></button>
            <button class="filter-btn" data-filter="digitization"><span>Data Digitization</span></button>
            <button class="filter-btn" data-filter="software"><span>Software Development</span></button>
            <button class="filter-btn" data-filter="it-support"><span>IT Support</span></button>
        </div>

        <!-- Gallery Slider -->
        <div class="gallery-grid" id="galleryGrid">
            @foreach($images as $image)
            <div class="gallery-item" data-category="{{ $image['category'] }}">
                <img src="{{ url('storage/gallery/' . basename($image['path'])) }}" alt="{{ $image['title'] }}" onclick="openLightbox(this, '{{ $image['title'] }}', '{{ $image['description'] }}')">
                <div class="gallery-caption">
                    <h3>{{ $image['title'] }}</h3>
                    <p>{{ $image['description'] }}</p>
                </div>
            </div>
            @endforeach

            <!-- Navigation arrows -->
            <button class="gallery-nav gallery-nav-prev" onclick="prevGallerySlide()">‹</button>
            <button class="gallery-nav gallery-nav-next" onclick="nextGallerySlide()">›</button>

            <!-- Indicators -->
            <div class="gallery-indicators" id="galleryIndicators"></div>
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
                <img src="{{ url('storage/gallery/' . basename($project['image'])) }}" alt="{{ $project['title'] }}">
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
    // Filter functionality and auto-switching gallery
    const filterButtons = document.querySelectorAll(".filter-btn");
    const galleryItems = document.querySelectorAll(".gallery-item");
    let currentGallerySlide = 0;
    let filteredItems = [];
    let galleryInterval;

    // Function to show gallery slide
    function showGallerySlide(index) {
        filteredItems.forEach(item => item.classList.remove('active'));
        if (filteredItems.length > 0) {
            filteredItems[index].classList.add('active');
        }
        updateIndicators();
    }

    // Function to update indicators
    function updateIndicators() {
        const indicatorsContainer = document.getElementById('galleryIndicators');
        indicatorsContainer.innerHTML = '';

        filteredItems.forEach((item, index) => {
            const indicator = document.createElement('div');
            indicator.className = 'gallery-indicator' + (index === currentGallerySlide ? ' active' : '');
            indicator.onclick = () => {
                currentGallerySlide = index;
                showGallerySlide(currentGallerySlide);
                startGallerySlider(); // Restart timer
            };
            indicatorsContainer.appendChild(indicator);
        });
    }

    // Function to start gallery auto-switching
    function startGallerySlider() {
        if (galleryInterval) clearInterval(galleryInterval);

        if (filteredItems.length > 1) {
            galleryInterval = setInterval(() => {
                currentGallerySlide = (currentGallerySlide + 1) % filteredItems.length;
                showGallerySlide(currentGallerySlide);
            }, 5000); // Switch every 5 seconds
        }
    }

    // Function to filter and show items
    function filterGallery(filter) {
        // Get filtered items
        filteredItems = Array.from(galleryItems).filter(item => {
            return filter === "all" || item.getAttribute("data-category") === filter;
        });

        // Hide all items first
        galleryItems.forEach(item => {
            item.classList.remove('active');
            item.style.display = 'none';
        });

        // Show filtered items
        filteredItems.forEach(item => {
            item.style.display = 'block';
        });

        // Reset to first slide
        currentGallerySlide = 0;
        if (filteredItems.length > 0) {
            showGallerySlide(0);
        }

        // Start auto-switching
        startGallerySlider();
    }

    // Initialize with all items
    filterGallery("all");

    // Filter button click handlers
    filterButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelector(".filter-btn.active").classList.remove("active");
            btn.classList.add("active");

            const filter = btn.getAttribute("data-filter");
            filterGallery(filter);
        });
    });

    // Make navigation functions global
    window.nextGallerySlide = function() {
        if (filteredItems.length > 0) {
            currentGallerySlide = (currentGallerySlide + 1) % filteredItems.length;
            showGallerySlide(currentGallerySlide);
            startGallerySlider(); // Restart timer
        }
    };

    window.prevGallerySlide = function() {
        if (filteredItems.length > 0) {
            currentGallerySlide = (currentGallerySlide - 1 + filteredItems.length) % filteredItems.length;
            showGallerySlide(currentGallerySlide);
            startGallerySlider(); // Restart timer
        }
    };

    /* Showcase slider - longer display time */
    let currentSlide = 0;
    const slides = document.querySelectorAll('#showcaseSlider .slide');
    if (slides.length > 0) {
        function showNextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        // Increased from 4000ms to 8000ms (8 seconds)
        setInterval(showNextSlide, 8000);
    }
});

// Improved Lightbox with better reliability
function openLightbox(img, title, description) {
    const lightbox = document.getElementById("lightbox");
    const lightboxImg = document.getElementById("lightbox-img");
    const lightboxCaption = document.getElementById("lightbox-caption");
    
    // Ensure lightbox exists
    if (!lightbox || !lightboxImg || !lightboxCaption) {
        console.error('Lightbox elements not found');
        return;
    }
    
    // Set image source and wait for it to load
    lightboxImg.onload = function() {
        lightbox.classList.add("active");
        document.body.style.overflow = 'hidden';
    };
    
    lightboxImg.onerror = function() {
        console.error('Failed to load image:', img.src);
    };
    
    lightboxImg.src = img.src;
    lightboxCaption.innerHTML = `<h3>${title}</h3><p>${description}</p>`;
}

function closeLightbox() {
    const lightbox = document.getElementById("lightbox");
    if (lightbox) {
        lightbox.classList.remove("active");
        document.body.style.overflow = 'auto';
        
        // Clear the image source to prevent memory issues
        const lightboxImg = document.getElementById("lightbox-img");
        if (lightboxImg) {
            lightboxImg.src = '';
        }
    }
}

// Enhanced event listeners with better error handling
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Ensure lightbox closes when clicking outside the image
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('close-lightbox')) {
                closeLightbox();
            }
        });
    }
});
</script>
@endpush
