@extends('layouts.public')

@section('title', 'Home | TeamO Digital Solutions')
@section('description', 'TeamO Digital Solutions (TDS) offers professional web development, IT consulting, software development, digitization, and digital transformation services.')
@section('keywords', 'TeamO Digital Solutions, Web Development, IT Consulting, Software Development, Digital Transformation, Digitization')

@php
    // Safe asset generation to avoid array access errors
    $baseUrl = request()->getSchemeAndHttpHost();
    $servicesBgUrl = $baseUrl . '/images/services-bg.jpg';
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/swiper/11.0.5/swiper-bundle.min.css">

<style>
 body {
    font-family: 'Inter', sans-serif;
    background-color: #ffffff;
    color: #000000;
    margin: 0;
    padding: 0;
}

:root {
    --black: #000000;
    --white: #ffffff;
    --light-blue: #1877F2;
}

/* Hero Section - White background with light blue accents */
.hero-section {
    background-color: #ffffff !important;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: var(--black);
    position: relative;
    z-index: 2;
    border: none !important;
    border-radius: 0 !important;
    margin: 0 !important;
    box-shadow: none !important;
    padding: 40px 20px !important;
}

.hero-section h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 3rem;
    line-height: 1.2;
    color: var(--black);
    text-shadow: 2px 2px 4px rgba(24, 119, 242, 0.3);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    width: 100%;
}

/* FIXED: Swiper Styles - Force white/transparent backgrounds and remove all blue */
.swiper,
.swiper-container,
.swiper-container-horizontal,
.swiper-wrapper,
.swiper-slide {
    background: transparent !important;
    background-color: transparent !important;
}

.swiper {
    width: 100% !important;
    max-width: 800px !important;
    margin: 0 auto !important;
    padding: 50px 0 !important;
    background: transparent !important;
    background-color: transparent !important;
    overflow: visible !important;
}

.swiper-wrapper {
    align-items: stretch !important;
    background: transparent !important;
    background-color: transparent !important;
    display: flex !important;
}

.swiper-slide {
    text-align: center !important;
    background: transparent !important;
    background-color: transparent !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    width: 100% !important;
    min-height: 450px !important;
    flex-shrink: 0 !important;
}

.services-slider {
    background: transparent !important;
    background-color: transparent !important;
    overflow: visible !important;
    width: 100% !important;
    max-width: 1200px !important;
    margin: 0 auto !important;
    aspect-ratio: 1 / 1 !important;
    min-height: 600px !important;
    height: 600px !important;
}

/* FIXED: Override ALL possible Swiper default backgrounds */
.swiper-container,
.swiper-container-horizontal,
.swiper-container-vertical,
.swiper-container-3d,
.swiper-container-multirow,
.swiper-container-free-mode {
    background: transparent !important;
    background-color: transparent !important;
}

/* FIXED: Service Cards - White background with light blue and black accents */
.service-card {
    background: #ffffff !important;
    color: var(--black) !important;
    border-radius: 20px !important;
    border: none !important;
    transition: all 0.5s ease !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    width: 100% !important;
    max-width: 1200px !important;
    height: 550px !important;
    margin: 0 auto !important;
    display: flex !important;
    flex-direction: column !important;
    position: relative !important;
    overflow: hidden !important;
    cursor: pointer !important;
    padding: 0 !important;
}

.service-card.expanded {
    min-height: 500px !important;
    max-height: 70vh !important;
    overflow-y: auto !important;
    transform: scale(1.02) !important;
    background: #ffffff !important;
}

.service-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12) !important;
    transform: translateY(-5px) !important;
    background: #ffffff !important;
}

.service-card.expanded:hover {
    background: #ffffff !important;
    color: var(--black) !important;
    border-color: var(--black) !important;
    transform: scale(1.02) translateY(-3px) !important;
}

/* Card Header - Icon left of title */
.card-header {
    padding: 2rem 2.5rem !important;
    text-align: left !important;
    border-bottom: none !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 1.5rem !important;
    background: linear-gradient(135deg, rgba(24, 119, 242, 0.03), transparent) !important;
    min-height: auto !important;
}

/* FIXED: Service Image Styles - Fixed conflicting rules */
.service-image {
    width: 100% !important;
    max-width: 800px !important;
    height: 200px !important;
    object-fit: contain !important;
    margin-bottom: 1.5rem !important;
    background: white !important;
    border-radius: 8px !important;
    padding: 10px !important;
    border: 2px solid var(--white) !important;
    transition: all 0.3s ease !important;
}

.service-card:hover .service-image {
    border-color: var(--black) !important;
    transform: scale(1.05) !important;
    background: white !important;
}

.service-icon-container {
    width: 90px !important;
    height: 90px !important;
    min-width: 90px !important;
    max-width: 90px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 !important;
    background: linear-gradient(135deg, #1877F2, #1565D8) !important;
    border-radius: 20px !important;
    transition: all 0.4s ease !important;
    flex-shrink: 0 !important;
    border: none !important;
    overflow: visible !important;
    z-index: 10 !important;
    position: relative !important;
    box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3) !important;
}

/* Emoji icon styling */
.service-icon {
    font-size: 3rem !important;
    line-height: 1 !important;
    display: inline-block !important;
    transition: all 0.3s ease !important;
    width: auto !important;
    height: auto !important;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15)) !important;
    text-align: center !important;
}

.service-card:hover .service-icon-container {
    background: var(--black) !important;
    transform: scale(1.05) !important;
}

.service-card:hover .service-icon {
    transform: scale(1.1) !important;
}

.service-title {
    color: var(--black) !important;
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    margin: 0 !important;
    line-height: 1.3 !important;
    transition: color 0.3s ease !important;
    text-align: left !important;
    flex: 1 !important;
}

/* Card Content */
.card-content {
    padding: 2rem 3rem !important;
    flex-grow: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    background: #ffffff !important;
}

.service-description-short,
.service-description-full {
    color: var(--black) !important;
    line-height: 1.8 !important;
    text-align: center !important;
    margin: 0 !important;
    font-size: 1.05rem !important;
    transition: color 0.3s ease !important;
}

.service-description-full ul {
    list-style: none !important;
    padding-left: 0 !important;
    margin: 15px 0 !important;
    text-align: center !important;
}

.service-description-full ul li {
    position: relative !important;
    padding-left: 0 !important;
    margin-bottom: 10px !important;
    line-height: 1.6 !important;
    text-align: center !important;
}

/* Remove all bullet points */
.service-description-full ul li::before {
    content: "" !important;
    display: none !important;
}

.service-description-full {
    animation: fadeInUp 0.4s ease-in-out !important;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Card Actions */
.card-actions {
    padding: 2rem 3rem 3rem !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 1.5rem !important;
    align-items: center !important;
    background: #ffffff !important;
}

.expand-btn {
    background: var(--light-blue) !important;
    color: var(--black) !important;
    border: 3px solid var(--light-blue) !important;
    padding: 15px 30px !important;
    border-radius: 30px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    font-size: 16px !important;
    min-width: 150px !important;
    justify-content: center !important;
}

.expand-btn:hover {
    background: var(--black) !important;
    color: var(--white) !important;
    border-color: var(--black) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
}

.expand-icon {
    transition: transform 0.4s ease !important;
    font-size: 14px !important;
}

.expand-btn.expanded .expand-icon {
    transform: rotate(180deg) !important;
}

.service-link-btn {
    background: #ffffff !important;
    color: var(--light-blue) !important;
    border: 3px solid var(--light-blue) !important;
    padding: 12px 25px !important;
    border-radius: 30px !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    font-size: 16px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.service-link-btn:hover {
    background: var(--black) !important;
    color: var(--white) !important;
    border-color: var(--black) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
}

/* FIXED: Swiper Controls - Remove blue backgrounds */
.swiper-button-next,
.swiper-button-prev {
    color: var(--black) !important;
    background: #ffffff !important;
    width: 60px !important;
    height: 60px !important;
    border-radius: 50% !important;
    margin-top: -30px !important;
    border: 3px solid var(--light-blue) !important;
    box-shadow: 0 6px 20px rgba(24, 119, 242, 0.4) !important;
    transition: all 0.3s ease !important;
    z-index: 10 !important;
}

.swiper-button-next:hover,
.swiper-button-prev:hover {
    background: var(--light-blue) !important;
    color: var(--black) !important;
    border-color: var(--black) !important;
    transform: scale(1.1) !important;
}

.swiper-button-next:after,
.swiper-button-prev:after {
    font-size: 24px !important;
    font-weight: bold !important;
    color: inherit !important;
}

.swiper-button-disabled {
    opacity: 0.5 !important;
}

/* FIXED: Pagination - Remove blue backgrounds */
.swiper-pagination {
    bottom: 20px !important;
    position: relative !important;
    margin-top: 30px !important;
}

.swiper-pagination-bullet {
    background: #ffffff !important;
    opacity: 0.6 !important;
    width: 15px !important;
    height: 15px !important;
    border: 2px solid var(--light-blue) !important;
    transition: all 0.3s ease !important;
    margin: 0 5px !important;
}

.swiper-pagination-bullet-active {
    opacity: 1 !important;
    background: var(--light-blue) !important;
    border-color: var(--black) !important;
    transform: scale(1.2) !important;
}

/* Wave Section - White background with light blue and black text */
.wave-section {
    background: linear-gradient(135deg, rgba(24, 119, 242, 0.02), #ffffff) !important;
    position: relative;
    overflow: hidden;
    color: var(--black);
    padding: 80px 20px;
    margin: 0;
    border-top: none !important;
    border-bottom: none !important;
}

.wave-section h2 {
    color: var(--light-blue) !important;
}

.wave-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="%231877F2"></path><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="%231877F2"></path><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%231877F2"></path></svg>') repeat-x;
    background-size: 1200px 120px;
    height: 120px;
    bottom: -1px;
    animation: wave 10s linear infinite;
    opacity: 0.3;
}

@keyframes wave {
    0% { background-position-x: 0; }
    100% { background-position-x: 1200px; }
}

.wave-text {
    font-size: 1.3rem;
    margin-bottom: 1rem;
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 1s ease-out forwards;
    line-height: 1.6;
    color: var(--black);
    position: relative;
    z-index: 2;
}

.wave-text:nth-child(2) { animation-delay: 0.3s; }
.wave-text:nth-child(3) { animation-delay: 0.6s; }
.wave-text:nth-child(4) { animation-delay: 0.9s; }

.cta-btn {
    display: inline-block;
    padding: 15px 30px;
    background: var(--light-blue);
    color: var(--black);
    font-weight: 600;
    border-radius: 50px;
    text-decoration: none;
    margin-top: 2rem;
    transition: all 0.3s ease;
    border: 2px solid var(--light-blue);
    position: relative;
    z-index: 2;
}

.cta-btn:hover {
    background: var(--black);
    color: var(--white);
    border-color: var(--black);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Scrollbar styling for expanded cards */
.service-card.expanded::-webkit-scrollbar {
    width: 8px;
}

.service-card.expanded::-webkit-scrollbar-track {
    background: rgba(24, 119, 242, 0.1);
    border-radius: 4px;
}

.service-card.expanded::-webkit-scrollbar-thumb {
    background: var(--light-blue);
    border-radius: 4px;
}

.service-card.expanded:hover::-webkit-scrollbar-thumb {
    background: var(--black);
}

/* Loading placeholder */
.loading-placeholder {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 400px;
    color: var(--black);
    font-size: 1.2rem;
}

/* Utility classes for consistent theming */
.text-primary { color: var(--light-blue) !important; }
.text-secondary { color: #000000 !important; }
.text-white { color: #ffffff !important; }

.bg-primary { background-color: var(--light-blue) !important; }
.bg-secondary { background-color: #000000 !important; }
.bg-white { background-color: #ffffff !important; }

.border-primary { border-color: var(--light-blue) !important; }
.border-secondary { border-color: #000000 !important; }

/* Override any remaining inconsistent colors */
.text-gray-600, .text-gray-700, .text-gray-800 {
    color: #000000 !important;
}

.text-gray-400, .text-gray-500 {
    color: var(--light-blue) !important;
}

.bg-gray-50, .bg-gray-100, .bg-gray-200 {
    background-color: #ffffff !important;
}

.border-gray-200, .border-gray-300 {
    border-color: var(--light-blue) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero-section {
        margin: 10px;
        min-height: 90vh;
        padding: 2rem 0;
    }

    .hero-section h1 {
        font-size: 2.5rem;
        margin-bottom: 2rem;
    }

    .swiper-button-next,
    .swiper-button-prev {
        width: 45px !important;
        height: 45px !important;
        margin-top: -22px !important;
    }

    .swiper-button-next:after,
    .swiper-button-prev:after {
        font-size: 18px !important;
    }

    .service-card {
        max-width: 90% !important;
        min-height: 350px !important;
    }

    .service-image {
        max-width: 150px !important;
        height: 120px !important;
    }

    .card-header {
        padding: 2rem 2rem 1.5rem !important;
    }

    .card-content {
        padding: 1.5rem 2rem !important;
    }

    .card-actions {
        padding: 1.5rem 2rem 2rem !important;
    }

    .service-icon {
        font-size: 3rem !important;
    }

    .service-title {
        font-size: 1.7rem !important;
    }

    .wave-text {
        font-size: 1.1rem;
    }
}

@media (max-width: 480px) {
    .hero-section h1 {
        font-size: 2rem;
    }

    .service-card {
        max-width: 95% !important;
        min-height: 320px !important;
    }

    .service-image {
        max-width: 120px !important;
        height: 100px !important;
    }

    .service-icon {
        font-size: 2.5rem !important;
    }

    .service-title {
        font-size: 1.5rem !important;
    }

    .card-header,
    .card-content,
    .card-actions {
        padding: 1.5rem !important;
    }

    .swiper-button-next,
    .swiper-button-prev {
        display: none !important;
    }

    .cta-btn {
        padding: 12px 24px;
    }
}




/* Clients Section - White background */
.clients-section {
    background: #ffffff; /* Already white */
    color: #000000;
    padding: 60px 20px;
    border-top: none !important;
    border-bottom: none !important;
}

.clients-section h2 {
    color: #000000;
    text-align: center;
    margin-bottom: 3rem;
    font-size: 2.5rem;
    font-weight: 700;
}

.clients-section h2 span:hover {
    color: var(--light-blue);
    transition: color 0.3s ease;
}

.client-logo-container {
    background: #ffffff; /* Already white */
    border: none !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    border-radius: 15px;
    height: 80px;
    width: 128px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.client-logo-container:hover {
    background: #ffffff; /* Keep white background */
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(24, 119, 242, 0.15);
}

/* Testimonials - White background */
.testimonials-section {
    background: #ffffff; /* Changed to white */
    color: #000000;
    padding: 80px 20px;
}

.testimonials-section h2 {
    color: #000000;
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 3rem;
}

.testimonial {
    background: #ffffff; /* Already white */
    color: #000000;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
    border: none !important;
    padding: 2rem;
}

.testimonial::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1877F2, #1565D8);
    border-radius: 20px 20px 0 0;
}

.testimonial:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 30px rgba(24, 119, 242, 0.15);
}

.testimonial h4 {
    color: #000000;
    font-weight: 600;
}

.testimonial p {
    color: #000000;
    line-height: 1.6;
}

.testimonial .author-info {
    color: var(--light-blue);
    font-weight: 500;
}

/* Blog Section - White background */
.blog-section {
    background: #ffffff; /* Already white */
    color: #000000;
    padding: 80px 20px;
}

.blog-section h2 {
    color: #000000;
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 3rem;
}

.blog-section p {
    color: #000000;
    text-align: center;
    font-size: 1.1rem;
    margin-bottom: 3rem;
}

.blog-card {
    background: #ffffff; /* Already white */
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    border: none !important;
}

.blog-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 30px rgba(24, 119, 242, 0.15);
}

.blog-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.blog-card:hover img {
    transform: scale(1.05);
}

.blog-card-content {
    padding: 2rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    background: #ffffff; /* Already white */
}

.blog-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #000000;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.blog-card-excerpt {
    color: #000000;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex-grow: 1;
}

.blog-category {
    display: inline-block;
    background: var(--light-blue);
    color: var(--black);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.blog-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.blog-card-date,
.blog-card-reading-time {
    color: var(--light-blue);
    font-size: 0.875rem;
    font-weight: 500;
}

.blog-card-date {
    display: flex;
    align-items: center;
}

.blog-card-reading-time {
    display: flex;
    align-items: center;
}

.read-more-btn {
    display: inline-block;
    padding: 12px 24px;
    background: var(--light-blue);
    color: var(--black); /* Black text */
    border-radius: 25px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-align: center;
    align-self: flex-start;
    border: 2px solid var(--light-blue);
}

.read-more-btn:hover {
    background: var(--black);
    border-color: var(--black);
    color: var(--white);
    transform: translateY(-2px);
}

.view-all-btn {
    display: inline-block;
    background: var(--light-blue); /* Light blue background */
    color: var(--black); /* Black text */
    padding: 15px 35px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid var(--light-blue);
    margin: 2rem auto;
    text-align: center;
}

.view-all-btn:hover {
    background: var(--black);
    color: var(--white);
    border-color: var(--black);
    transform: translateY(-2px);
}

/* Clients logos auto-scroll */
@keyframes scrollX {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%); /* move left by half the content width */
  }
}

.animate-scroll {
  display: flex;
  animation: scrollX 20s linear infinite;
}



</style>
@endpush

@section('content')

   <!-- Hero Section with Expandable Services Slider -->
    <section class="hero-section relative w-full mx-auto pt-8 py-16 rounded-lg shadow-lg overflow-hidden">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-center mb-8 drop-shadow-lg" style="color: var(--black);">
               Transform Your Analog Data Into Digital Formats
            </h1>

            <!-- Swiper Slider -->
            <div class="swiper services-slider" style="padding: 20px 0;">
                <div class="swiper-wrapper">
                    @foreach($services as $index => $service)
                    <!-- {{ $service['title'] }} -->
                    <div class="swiper-slide">
                        <div class="service-card expandable-card" data-service-id="{{ $index + 1 }}">
                            <div class="card-header">
                                <div class="service-icon-container">
                                    <span class="service-icon">
                                        @if($service['id'] == 'records-digitalisation')
                                            📄
                                        @elseif($service['id'] == 'custom-software')
                                            💻
                                        @elseif($service['id'] == 'it-support')
                                            🎧
                                        @elseif($service['id'] == 'consultancy')
                                            💡
                                        @else
                                            ⚙️
                                        @endif
                                    </span>
                                </div>
                                <h3 class="service-title">{{ $service['title'] }}</h3>
                            </div>

                            <div class="card-content">
                                <p class="service-description-short">
                                    {{ $service['intro'] }}
                                </p>
                                <p class="service-description-full" style="display: none;">
                                    {{ $service['intro'] }}

                                    @if(isset($service['features']))
                                    <br><br><strong style="color: #1877F2; font-size: 1.1rem;">Features:</strong>
                                    <ul>
                                        @foreach($service['features'] as $feature)
                                        <li>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                    @endif

                                    @if(isset($service['benefits']))
                                    <br><strong style="color: #1877F2; font-size: 1.1rem;">What you get:</strong>
                                    <ul>
                                        @foreach($service['benefits'] as $benefit)
                                        <li>{{ $benefit }}</li>
                                        @endforeach
                                    </ul>
                                    @endif

                                    @if(isset($service['services']))
                                    <br><strong>Our Services:</strong>
                                    @foreach($service['services'] as $item)
                                    <br><br><strong>{{ $item['name'] }}:</strong> {{ $item['description'] }}
                                    @endforeach
                                    @endif

                                    @if(isset($service['footer']))
                                    <br><br><em>{{ $service['footer'] }}</em>
                                    @endif

                                    @if(isset($service['note']) && $service['note'])
                                    <br><br><strong>Note:</strong> {{ $service['note'] }}
                                    @endif
                                </p>
                            </div>

                            <div class="card-actions">
                                <button class="expand-btn" onclick="toggleExpand(this)">
                                    <span class="expand-text">Read More</span>
                                    <i class="fas fa-chevron-down expand-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Swiper controls -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>




<!-- Why Us Section -->
<section class="wave-section text-center py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Why Choose TeamO Digital Solutions (TDS)?</h2>
        <div class="max-w-3xl mx-auto">
            <p class="wave-text">We digitize your organization's entire historical archive,</p>
            <p class="wave-text">from inception to the present,</p>
            <p class="wave-text">into a seamless centralized digital system.</p>
            <p class="wave-text">Keep your records safe, organised and easy to access when you
need them.</p>
        </div>
        @if(Route::has('services'))
            <a href="{{ route('services') }}" class="cta-btn">Explore Our Services</a>
        @else
            <a href="/services" class="cta-btn">Explore Our Services</a>
        @endif
    </div>
</section>

<!-- Clients Section -->
<section class="clients-section py-16 overflow-hidden relative">
    <h2 class="text-4xl font-bold text-center mb-12">
        <span class="hover:text-cornflower transition-colors cursor-default">Our Trusted Clients</span>
    </h2>

    <div class="w-full flex justify-center relative overflow-hidden">
        <div class="flex space-x-12 animate-scroll w-max">
            {{-- First loop for scrolling --}}
            <div class="client-logo-container">
                <img src="{{ asset('images/client1.jpg') }}"
                     class="max-h-16 max-w-full object-contain"
                     alt="Client 1 Logo"
                     loading="lazy">
            </div>
            <div class="client-logo-container">
                <img src="{{ asset('images/client2.jpg') }}"
                     class="max-h-16 max-w-full object-contain"
                     alt="Client 2 Logo"
                     loading="lazy">
            </div>

        </div>
    </div>
</section>


<!-- Testimonials -->
<section class="testimonials-section py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">What Our Clients Say</h2>

        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($testimonials as $testimonial)
                <div class="testimonial p-8 rounded-2xl border border-gray-200 shadow-sm bg-white">
                    <div class="flex items-center mb-4">
                        @php
                            $img = $testimonial->client_image;
                            $imgPath = 'storage/testimonials/' . ltrim((string)$img, '/');
                        @endphp

                        @if(!empty($img) && file_exists(public_path($imgPath)))
                            <img src="{{ asset($imgPath) }}"
                                 alt="{{ $testimonial->client_name }}"
                                 class="h-12 w-12 object-cover mr-4 rounded-full p-1 ring-2 ring-cornflower"
                                 loading="lazy">
                        @else
                            <div class="h-12 w-12 rounded-full mr-4 flex items-center justify-center bg-cornflower text-white">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif

                        <div>
                            <h4 class="font-semibold">{{ $testimonial->client_name }}</h4>
                            @if(!empty($testimonial->client_position))
                                <p class="text-sm text-gray-600">{{ $testimonial->client_position }}</p>
                            @endif
                        </div>
                    </div>

                    @if(!empty($testimonial->testimonial))
                        <p class="italic leading-relaxed text-gray-800">"{{ $testimonial->testimonial }}"</p>
                    @endif

                    @if(!empty($testimonial->rating))
                        <div class="mt-3 text-yellow-500">
                            @php
                                $full = floor($testimonial->rating);
                                $half = ($testimonial->rating - $full) >= 0.5;
                            @endphp
                            @for($i = 0; $i < $full; $i++)
                                <i class="fas fa-star"></i>
                            @endfor
                            @if($half)
                                <i class="fas fa-star-half-alt"></i>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-3">
                    <div class="empty-state mx-auto max-w-md text-center p-8 rounded-2xl border border-dashed">
                        <i class="fas fa-comments text-3xl text-gray-400"></i>
                        <h3 class="mt-3 text-lg font-semibold">No Testimonials Yet</h3>
                        <p class="text-gray-600">We're working hard to serve our clients. Testimonials coming soon!</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>




<!-- Blogs Section -->
<section class="blog-section py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Latest Insights & Updates</h2>
            <p class="text-lg max-w-2xl mx-auto">
                Stay informed with our latest thoughts on digital transformation, technology trends, and industry insights.
            </p>
        </div>

        @if(isset($blogs) && $blogs->isNotEmpty())
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($blogs->take(6) as $blog)
                    <article class="blog-card">
                        @if($blog->featured_image)
                            <img src="{{ asset('storage/' . $blog->featured_image) }}"
                                 alt="{{ $blog->featured_image_alt ?? $blog->title }}"
                                 loading="lazy">
                        @elseif($blog->thumbnail)
                            <img src="{{ $baseUrl . '/' . ltrim($blog->thumbnail, '/') }}"
                                 alt="{{ $blog->title }}"
                                 loading="lazy">
                        @else
                            <div class="image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif

                        <div class="blog-card-content">
                            <h3 class="blog-card-title">{{ $blog->title }}</h3>

                            <p class="blog-card-excerpt">
                                {{ $blog->meta_description ?? Str::limit(strip_tags($blog->introduction ?? $blog->content ?? ''), 120) }}
                            </p>

                            @if($blog->category)
                                <span class="blog-category">
                                    <i class="fas fa-tag mr-1"></i>
                                    {{ ucwords(str_replace('-', ' ', $blog->category)) }}
                                </span>
                            @endif

                            <div class="blog-card-meta">
                                <div class="blog-card-date">
                                    <i class="far fa-calendar-alt mr-2"></i>
                                    {{ $blog->published_at ? $blog->published_at->format('M d, Y') : $blog->created_at->format('M d, Y') }}
                                </div>
                                @if($blog->reading_time)
                                    <div class="blog-card-reading-time">
                                        <i class="far fa-clock mr-2"></i>
                                        {{ $blog->reading_time }} min read
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('blog.show', $blog->slug) }}" class="read-more-btn">
                                Read More <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="text-center mt-12">
                @if(Route::has('blog'))
                    <a href="{{ route('blog') }}" class="view-all-btn">
                        View All Posts <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                @else
                    <a href="/blog" class="view-all-btn">
                        View All Posts <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                @endif
            </div>
        @else
            <div class="empty-state mx-auto max-w-md">
                <i class="fas fa-blog"></i>
                <h3>Coming Soon</h3>
                <p>We're preparing amazing content for you. Stay tuned!</p>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/swiper/11.0.5/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Swiper for one service at a time WITH AUTOPLAY
    const servicesSwiper = new Swiper('.services-slider', {
        slidesPerView: 1,              // Show only one service card at a time
        spaceBetween: 0,               // No space between slides
        loop: true,                    // Infinite loop
        centeredSlides: true,          // Center the active slide
        autoplay: {
            delay: 5000,               // 5 seconds per slide
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'slide',               // Slide effect
        speed: 800,                    // Transition speed
        allowTouchMove: true,          // Enable swipe navigation
        grabCursor: true,              // Show grab cursor
        watchOverflow: true,
        watchSlidesProgress: true,
        watchSlidesVisibility: true,
    });

    // Function to toggle card expansion
    window.toggleExpand = function(button) {
        const card = button.closest('.expandable-card');
        const shortDesc = card.querySelector('.service-description-short');
        const fullDesc = card.querySelector('.service-description-full');
        const expandText = button.querySelector('.expand-text');
        const expandIcon = button.querySelector('.expand-icon');

        const isExpanded = card.classList.contains('expanded');

        if (isExpanded) {
            // Collapse
            card.classList.remove('expanded');
            button.classList.remove('expanded');
            shortDesc.style.display = 'block';
            fullDesc.style.display = 'none';
            expandText.textContent = 'Read More';
            expandIcon.style.transform = 'rotate(0deg)';
        } else {
            // Collapse other expanded cards
            document.querySelectorAll('.expandable-card.expanded').forEach(otherCard => {
                if (otherCard !== card) {
                    const otherButton = otherCard.querySelector('.expand-btn');
                    if (otherButton) toggleExpand(otherButton);
                }
            });

            // Expand current
            card.classList.add('expanded');
            button.classList.add('expanded');
            shortDesc.style.display = 'none';
            fullDesc.style.display = 'block';
            expandText.textContent = 'Show Less';
            expandIcon.style.transform = 'rotate(180deg)';
        }
    };

    // Close expanded cards when clicking outside
    document.addEventListener('click', function(e) {
        if (
            !e.target.closest('.expandable-card') &&
            !e.target.closest('.swiper-button-next') &&
            !e.target.closest('.swiper-button-prev')
        ) {
            document.querySelectorAll('.expandable-card.expanded').forEach(card => {
                const button = card.querySelector('.expand-btn');
                if (button) toggleExpand(button);
            });
        }
    });

    // Prevent accidental card collapse when clicking Swiper controls
    document.addEventListener('click', function(e) {
        if (
            e.target.closest('.swiper-button-next') ||
            e.target.closest('.swiper-button-prev') ||
            e.target.closest('.swiper-pagination')
        ) {
            e.stopPropagation();
        }
    });

    // Intersection Observer for fade-in animations (blogs/testimonials)
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.blog-card, .testimonial').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>

@endpush
