@extends('layouts.public')

@section('title', 'Blog | TeamO Digital Solutions')
@section('description', 'Stay updated with the latest insights, tips, and trends in web development, IT consulting, and digital transformation from TeamO Digital Solutions.')
@section('keywords', 'TeamO Digital Solutions, Blog, Web Development, IT Consulting, Digital Transformation, Technology News')

@push('styles')


<style>
    body { 
        font-family: 'Inter', sans-serif; 
        background-color: #ffffff;
    }

    .hero {
        background: #ffffff;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        z-index: 2;
        overflow: hidden;
        border-bottom: 2px solid #93c5fd;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(147, 197, 253, 0.05);
    }

    .hero .container {
        position: relative;
        z-index: 1;
    }

    .hero h1 {
        color: #000000;
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .hero p {
        font-size: 1.2rem;
        color: #1e40af;
        max-width: 600px;
        margin: 0 auto;
    }

    .blog-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 60px 20px;
        background: #ffffff;
    }

    .blog-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .blog-header h2 {
        font-size: 2.5rem;
        color: #000000;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .blog-header p {
        font-size: 1.1rem;
        color: #1e40af;
        max-width: 600px;
        margin: 0 auto;
    }

    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }

    .blog-card {
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(147, 197, 253, 0.2);
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        height: fit-content;
        border: none;
    }

    .blog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
    }

    .blog-card img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .blog-card:hover img {
        transform: scale(1.05);
    }

    .blog-card-body {
        padding: 25px;
    }

    .blog-card-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #000000;
        margin-bottom: 12px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blog-card-excerpt {
        font-size: 0.95rem;
        color: #1e40af;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blog-card-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 0.85rem;
        color: #1e40af;
    }

    .blog-card-date {
        display: flex;
        align-items: center;
        color: #1e40af;
    }

    .blog-card-date i {
        margin-right: 5px;
    }

    .like-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .like-button {
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        color: #1e40af;
        transition: all 0.3s ease;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .like-button:hover {
        background: #f0f9ff;
        color: #000000;
        border: none;
    }

    .like-button.liked {
        color: #000000;
        background: #f0f9ff;
    }

    .like-count {
        font-weight: 500;
    }

    .read-more-btn {
        display: inline-block;
        padding: 10px 20px;
        background: #1e40af;
        color: #ffffff;
        border-radius: 25px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .read-more-btn:hover {
        background: #000000;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3);
    }

    .no-posts {
        text-align: center;
        padding: 80px 20px;
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(147, 197, 253, 0.2);
        border: none;
    }

    .no-posts i {
        font-size: 4rem;
        color: #93c5fd;
        margin-bottom: 20px;
    }

    .no-posts h3 {
        font-size: 1.5rem;
        color: #000000;
        margin-bottom: 10px;
    }

    .no-posts p {
        color: #1e40af;
    }

    .no-posts a {
        color: #1e40af;
    }

    .no-posts a:hover {
        color: #000000;
    }

    /* Search and Filter Section */
    .blog-filters {
        background: #ffffff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(147, 197, 253, 0.2);
        margin-bottom: 40px;
        border: none;
    }

    .filter-row {
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
    }

    .search-box input {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
        background: #ffffff;
        color: #000000;
    }

    .search-box input:focus {
        outline: none;
        border-color: #1e40af;
    }

    .search-box input::placeholder {
        color: #1e40af;
    }

    .category-filter select {
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        background: #ffffff;
        cursor: pointer;
        color: #000000;
    }

    .category-filter select:focus {
        outline: none;
        border-color: #1e40af;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 60px;
    }

    .pagination {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .pagination a,
    .pagination span {
        padding: 10px 15px;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        color: #1e40af;
        transition: all 0.3s ease;
        background: #ffffff;
    }

    .pagination a:hover {
        background: #1e40af;
        color: #ffffff;
        border-color: #1e40af;
    }

    .pagination .active {
        background: #000000;
        color: #ffffff;
        border-color: #000000;
    }

    .pagination .disabled {
        color: #93c5fd;
        background: #ffffff;
        border-color: #93c5fd;
        cursor: not-allowed;
    }

    /* Newsletter Section */
    .newsletter-section {
        background: #ffffff;
        position: relative;
        overflow: hidden;
    }

    .newsletter-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(147, 197, 253, 0.05);
    }

    .newsletter-content {
        position: relative;
        z-index: 1;
        text-align: center;
        padding: 60px 20px;
        border-radius: 15px;
        border: none;
        background: #ffffff;
        backdrop-filter: blur(10px);
    }

    .newsletter-content h2 {
        color: #000000;
        font-size: 3rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .newsletter-content p {
        color: #1e40af;
        font-size: 1.2rem;
        margin-bottom: 2rem;
    }

    .newsletter-content input {
        padding: 12px 16px;
        border-radius: 8px;
        border: none;
        color: #000000;
        font-size: 16px;
        min-width: 250px;
        background: #ffffff;
    }

    .newsletter-content input:focus {
        outline: none;
        border-color: #1e40af;
    }

    .newsletter-content input::placeholder {
        color: #1e40af;
    }

    .newsletter-content button {
        background: #1e40af;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .newsletter-content button:hover {
        background: #000000;
        transform: translateY(-2px);
    }

    /* Category badges */
    .bg-blue-100.text-blue-800 {
        background: #f0f9ff !important;
        color: #1e40af !important;
        border: none;
    }

    /* Reading time styling */
    .text-sm.text-gray-500 {
        color: #1e40af;
    }

    /* Newsletter section override */
    .bg-gradient-to-r.from-blue-600.to-purple-600 {
        background: #ffffff !important;
        border: none;
        border-radius: 15px;
        margin: 40px 20px;
    }

    .bg-gradient-to-r.from-blue-600.to-purple-600 .text-white {
        color: #000000 !important;
    }

    .bg-gradient-to-r.from-blue-600.to-purple-600 h2 {
        color: #000000 !important;
    }

    .bg-gradient-to-r.from-blue-600.to-purple-600 p {
        color: #1e40af !important;
    }

    .bg-gradient-to-r.from-blue-600.to-purple-600 input {
        color: #000000 !important;
        background: #ffffff !important;
        border: 2px solid #93c5fd !important;
    }

    .bg-gradient-to-r.from-blue-600.to-purple-600 input::placeholder {
        color: #1e40af !important;
    }

    .bg-gradient-to-r.from-blue-600.to-purple-600 button {
        background: #1e40af !important;
        color: #ffffff !important;
    }

    .bg-gradient-to-r.from-blue-600.to-purple-600 button:hover {
        background: #000000 !important;
    }

    .text-green-200 {
        color: #1e40af !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2.5rem;
        }

        .blog-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .blog-container {
            padding: 40px 15px;
        }

        .filter-row {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            min-width: 100%;
        }

        .newsletter-content form {
            flex-direction: column;
            gap: 15px;
        }

        .newsletter-content input {
            min-width: 100%;
        }
    }

    /* Loading animation */
    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 60px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #1e40af;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Default thumbnail gradient override */
    .bg-gradient-to-br.from-blue-500.to-purple-600 {
        background: linear-gradient(135deg, #93c5fd, #1e40af) !important;
    }
</style>
@endpush


@section('content')
<!-- Hero Section -->
<header class="hero">
    <div class="container mx-auto px-4">
        <h1>Latest Insights</h1>
        <p>Discover the latest trends, tips, and insights in digital transformation, web development, and IT consulting</p>
    </div>
</header>

<!-- Blog Content -->
<div class="blog-container">
    <!-- Search and Filter Section -->
    <div class="blog-filters">
        <div class="filter-row">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search articles..." value="{{ request('search') }}">
            </div>
            <div class="category-filter">
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="web-development" {{ request('category') == 'web-development' ? 'selected' : '' }}>Web Development</option>
                    <option value="it-consulting" {{ request('category') == 'it-consulting' ? 'selected' : '' }}>IT Consulting</option>
                    <option value="digital-transformation" {{ request('category') == 'digital-transformation' ? 'selected' : '' }}>Digital Transformation</option>
                    <option value="technology" {{ request('category') == 'technology' ? 'selected' : '' }}>Technology</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Blog Header -->
    <div class="blog-header">
        <h2>Our Blog</h2>
        <p>Stay informed with expert insights and industry knowledge from our team of professionals</p>
    </div>

    <!-- Blog Grid -->
    <div class="blog-grid" id="blogGrid">
        @forelse($blogs as $blog)
            <article class="blog-card" data-category="{{ $blog->category ?? '' }}">
                @if($blog->thumbnail)
                    <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ $blog->title }}" loading="lazy">
                @else
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 h-56 flex items-center justify-center">
                        <i class="fas fa-newspaper text-4xl text-white opacity-50"></i>
                    </div>
                @endif
                
                <div class="blog-card-body">
                    <div class="blog-card-meta">
                        <div class="blog-card-date">
                            <i class="far fa-calendar-alt"></i>
                            {{ $blog->created_at->format('M d, Y') }}
                        </div>
                        @if($blog->category)
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                {{ $blog->category_name }}
                            </span>
                        @endif
                    </div>
                    
                    <h3 class="blog-card-title">{{ $blog->title }}</h3>
                    <p class="blog-card-excerpt">{{ $blog->excerpt }}</p>
                    
                    <!-- Reading time -->
                    <div class="text-sm text-gray-500 mb-3">
                        <i class="far fa-clock"></i> {{ $blog->reading_time }}
                    </div>
                    
                    <div class="like-section">
                        <button class="like-button" data-blog-id="{{ $blog->id }}">
                            <i class="far fa-heart heart-icon"></i>
                            <span class="like-count">{{ $blog->likes ?? 0 }}</span>
                        </button>
                        
                        <a href="{{ route('blog.show', $blog->slug) }}" class="read-more-btn">
                            Read More
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="no-posts">
                <i class="fas fa-newspaper"></i>
                <h3>No Blog Posts Found</h3>
                <p>
                    @if(request('search') || request('category'))
                        No articles match your current search or filter criteria. 
                        <a href="{{ route('blog.index') }}" class="text-blue-600 underline">View all articles</a>
                    @else
                        We're working on creating amazing content for you. Check back soon!
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($blogs->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination">
                {{-- Previous Page Link --}}
                @if ($blogs->onFirstPage())
                    <span class="disabled">&laquo; Previous</span>
                @else
                    <a href="{{ $blogs->previousPageUrl() }}">&laquo; Previous</a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($blogs->getUrlRange(1, $blogs->lastPage()) as $page => $url)
                    @if ($page == $blogs->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($blogs->hasMorePages())
                    <a href="{{ $blogs->nextPageUrl() }}">Next &raquo;</a>
                @else
                    <span class="disabled">Next &raquo;</span>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Newsletter Signup -->
<section class="bg-gradient-to-r from-blue-600 to-purple-600 py-16 text-white">
    <div class="flex justify-center">
        <div class="text-center px-6 py-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg">
            <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
            <p class="text-xl mb-8 opacity-90">Subscribe to our newsletter for the latest insights and updates</p>
            
            <form class="flex gap-4 justify-center" action="{{ route('newsletter.subscribe') }}" method="POST">
                @csrf
                <input type="email" name="email" placeholder="Enter your email" 
                       class="px-4 py-3 rounded-lg text-gray-900" required>
                <button type="submit" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Subscribe
                </button>
            </form>
            
            @if (session('newsletter_success'))
                <div class="mt-4 text-green-200">
                    {{ session('newsletter_success') }}
                </div>
            @endif
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');

    // Search and filter functionality
    function performSearch() {
        const searchValue = searchInput.value;
        const categoryValue = categoryFilter.value;
        
        // Build URL with parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('page'); // Reset pagination on new search
        
        if (searchValue) {
            url.searchParams.set('search', searchValue);
        } else {
            url.searchParams.delete('search');
        }
        
        if (categoryValue) {
            url.searchParams.set('category', categoryValue);
        } else {
            url.searchParams.delete('category');
        }
        
        window.location.href = url.toString();
    }

    // Debounce search input
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500);
    });

    categoryFilter.addEventListener('change', performSearch);

    // Like functionality
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', function() {
            const blogId = this.dataset.blogId;
            const likeCount = this.querySelector('.like-count');
            const heartIcon = this.querySelector('.heart-icon');

            // Optimistic UI update
            const currentCount = parseInt(likeCount.textContent);
            const isLiked = this.classList.contains('liked');

            if (isLiked) {
                likeCount.textContent = currentCount - 1;
                this.classList.remove('liked');
                heartIcon.classList.remove('fas');
                heartIcon.classList.add('far');
            } else {
                likeCount.textContent = currentCount + 1;
                this.classList.add('liked');
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas');
            }

            // Send AJAX request
            const url = `{{ route('blog.like', ['id' => '__id__']) }}`.replace('__id__', blogId);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    blog_id: blogId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    likeCount.textContent = data.likes;
                    
                    // Update liked state based on server response
                    if (data.is_liked !== undefined) {
                        if (data.is_liked) {
                            this.classList.add('liked');
                            heartIcon.classList.remove('far');
                            heartIcon.classList.add('fas');
                        } else {
                            this.classList.remove('liked');
                            heartIcon.classList.remove('fas');
                            heartIcon.classList.add('far');
                        }
                    }
                } else {
                    // Revert optimistic update on error
                    if (isLiked) {
                        likeCount.textContent = currentCount;
                        this.classList.add('liked');
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas');
                    } else {
                        likeCount.textContent = currentCount;
                        this.classList.remove('liked');
                        heartIcon.classList.remove('fas');
                        heartIcon.classList.add('far');
                    }
                    console.error('Like action failed:', data.message);
                }
            })
            .catch(error => {
                // Revert optimistic update on error
                if (isLiked) {
                    likeCount.textContent = currentCount;
                    this.classList.add('liked');
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                } else {
                    likeCount.textContent = currentCount;
                    this.classList.remove('liked');
                    heartIcon.classList.remove('fas');
                    heartIcon.classList.add('far');
                }
                console.error('Network error:', error);
            });
        });
    });
});
</script>
@endpush
