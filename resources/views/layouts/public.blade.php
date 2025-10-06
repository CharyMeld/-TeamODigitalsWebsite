<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TeamO Digital Solutions | Web Development, IT Consulting & Digital Transformation')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('description', 'TeamO Digital Solutions (TDS) offers web development, IT consulting, software development, digitization, and digital transformation services.')">
    <meta name="keywords" content="@yield('keywords', 'TeamO Digital Solutions, web development, IT consulting, software development, digitization, digital transformation, SEO, IT support')">
    <meta name="author" content="TeamO Digital Solutions">
    <meta name="robots" content="index, follow">

    @php
        // Safe URL generation to avoid array access errors
        $currentUrl = request()->url();
        $baseUrl = request()->getSchemeAndHttpHost();
        $logoUrl = $baseUrl . '/images/logo.png';
        $ogImageUrl = $baseUrl . '/images/og-preview.jpg';
        $faviconUrl = $baseUrl . '/images/favicon.ico';
    @endphp

    <!-- Open Graph for social sharing -->
    <meta property="og:title" content="@yield('og_title', 'TeamO Digital Solutions')">
    <meta property="og:description" content="@yield('og_description', 'Your trusted partner in IT consulting, software development, and digital transformation.')">
    <meta property="og:image" content="@yield('og_image', '{{ $ogImageUrl }}')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $currentUrl }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', 'TeamO Digital Solutions')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Your trusted partner in IT consulting, software development, and digital transformation.')">
    <meta name="twitter:image" content="@yield('twitter_image', '{{ $ogImageUrl }}')">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $currentUrl }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons & External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- Tailwind CSS -->
    @vite(['resources/css/public.css', 'resources/js/app.js'])

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Custom Styles -->
    <style>

    </style>

    @stack('styles')
</head>

<body class="bg-gray-100">
    <!-- Header - Single Bar -->
    <header class="site-header">
        <div class="header-top">
            <div class="header-container">
                <!-- Logo/Brand on the left -->
                <div class="brand">
                    <img class="logo" src="{{ $logoUrl }}" alt="TeamO Digital Solutions logo" />
                    <h1></h1>
                </div>

                <!-- Navigation Menu in the center -->
                <nav id="primaryNav" class="main-nav" role="navigation" aria-label="Primary">
                    <div class="nav-container">
                        <ul>
                            @if(Route::has('home'))
                            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
                            @else
                            <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Home</a></li>
                            @endif

                            @if(Route::has('about'))
                            <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
                            @else
                            <li><a href="/about" class="{{ request()->is('about') ? 'active' : '' }}">About</a></li>
                            @endif

                            @if(Route::has('services'))
                            <li><a href="{{ route('services') }}" class="{{ request()->routeIs('services') ? 'active' : '' }}">Services</a></li>
                            @else
                            <li><a href="/services" class="{{ request()->is('services') ? 'active' : '' }}">Services</a></li>
                            @endif

                            @if(Route::has('blog'))
                            <li><a href="#" class="{{ request()->routeIs('blog*') ? 'active' : '' }}">Blog</a></li>
                            @else
                            <li><a href="#" class="{{ request()->is('blog*') ? 'active' : '' }}">Blog</a></li>
                            @endif

                            @if(Route::has('contact'))
                            <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
                            @else
                            <li><a href="/contact" class="{{ request()->is('contact') ? 'active' : '' }}">Contact</a></li>
                            @endif

                            @if(Route::has('gallery'))
                            <li><a href="{{ route('gallery') }}" class="{{ request()->routeIs('gallery') ? 'active' : '' }}">Gallery</a></li>
                            @else
                            <li><a href="/gallery" class="{{ request()->is('gallery') ? 'active' : '' }}">Gallery</a></li>
                            @endif

                            @if(Route::has('careers.index'))
                            <li><a href="{{ route('careers.index') }}" class="{{ request()->routeIs('careers.*') ? 'active' : '' }}">Careers</a></li>
                            @else
                            <li><a href="/careers" class="{{ request()->is('careers*') ? 'active' : '' }}">Careers</a></li>
                            @endif

                        </ul>
                    </div>
                </nav>

                <!-- Search box on the right -->
                <div class="header-right">
                    @if(Route::has('search'))
                    <form class="search-form" action="{{ route('search') }}" method="GET" role="search" aria-label="Site search">
                        <input type="text" name="q" placeholder="Search…" aria-label="Search site" />
                        <button type="submit" aria-label="Submit search">🔍</button>
                    </form>
                    @else
                    <form class="search-form" action="#" method="GET" role="search" aria-label="Site search">
                        <input type="text" name="q" placeholder="Search…" aria-label="Search site" />
                        <button type="submit" aria-label="Submit search">🔍</button>
                    </form>
                    @endif
                    <button id="navToggle" class="hamburger" aria-controls="primaryNav" aria-expanded="false" aria-label="Toggle menu">☰</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-top">
                <!-- Quick Links -->
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        @if(Route::has('home'))
                        <li><a href="{{ route('home') }}">Home</a></li>
                        @else
                        <li><a href="/">Home</a></li>
                        @endif

                        @if(Route::has('about'))
                        <li><a href="{{ route('about') }}">About Us</a></li>
                        @else
                        <li><a href="/about">About Us</a></li>
                        @endif

                        @if(Route::has('services'))
                        <li><a href="{{ route('services') }}">Services</a></li>
                        @else
                        <li><a href="/services">Services</a></li>
                        @endif

                        @if(Route::has('blog'))
                        <li><a href="#">Blog</a></li>
                        @else
                        <li><a href="#">Blog</a></li>
                        @endif

                        @if(Route::has('contact'))
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                        @else
                        <li><a href="/contact">Contact</a></li>
                        @endif

                        @if(Route::has('login'))
                        <li><a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a></li>
                        @else
                        <li><a href="/login" class="{{ request()->is('login') ? 'active' : '' }}">Login</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="footer-col">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="footer-col footer-col-wide">
                    <h4>Contact Information</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Plot 40, Rasco Close, Sasa, Ibadan, Oyo State, Nigeria</p>
                    <p><i class="fas fa-phone"></i> +234 814 446 6160</p>
                    <p><i class="fas fa-envelope"></i> teamodigitalsolutions@gmail.com</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} TeamO Digital Solutions. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Mobile Navigation Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.getElementById('navToggle');
            const primaryNav = document.getElementById('primaryNav');

            if (navToggle && primaryNav) {
                navToggle.addEventListener('click', function() {
                    const isOpen = primaryNav.classList.contains('open');

                    if (isOpen) {
                        primaryNav.classList.remove('open');
                        navToggle.setAttribute('aria-expanded', 'false');
                    } else {
                        primaryNav.classList.add('open');
                        navToggle.setAttribute('aria-expanded', 'true');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
