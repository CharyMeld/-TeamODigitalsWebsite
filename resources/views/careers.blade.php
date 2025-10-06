@extends('layouts.public')

@section('title', $page_title ?? 'Careers - TeamO Digital Solutions')
@section('description', $meta_description ?? 'Join our team at TeamO Digital Solutions')
@section('keywords', 'careers, jobs, employment, TeamO Digital Solutions, job opportunities, vacancies')
@section('og_title', $og_title ?? 'Careers - TeamO Digital Solutions')
@section('og_description', $og_description ?? 'Join our team at TeamO Digital Solutions')
@section('og_type', 'website')

@push('styles')
<style>
    body {
        background-color: #ffffff;
        font-family: 'Inter', sans-serif;
    }

    .careers-hero {
        background: #ffffff;
        padding: 80px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .careers-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(24, 119, 242, 0.05), transparent);
    }

    .careers-hero .container {
        position: relative;
        z-index: 1;
    }

    .careers-hero h1 {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #000000;
    }

    .careers-hero p {
        font-size: 1.2rem;
        color: #1877F2;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .jobs-section {
        background: #ffffff;
        padding: 60px 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .jobs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .job-card {
        background: #ffffff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        padding: 30px;
        position: relative;
    }

    .job-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(24, 119, 242, 0.2);
    }

    .job-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #1877F2;
        color: #ffffff;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        text-transform: uppercase;
        margin-bottom: 15px;
    }

    .job-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #000000;
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }

    .job-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 0.95rem;
    }

    .job-meta-item i {
        color: #1877F2;
        font-size: 1rem;
    }

    .job-description {
        color: #333;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .job-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .job-deadline {
        font-size: 0.9rem;
        color: #666;
    }

    .apply-btn {
        background: #1877F2;
        color: #ffffff;
        padding: 10px 24px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .apply-btn:hover {
        background: #000000;
        transform: translateX(5px);
    }

    .no-jobs {
        text-align: center;
        padding: 80px 20px;
    }

    .no-jobs h3 {
        font-size: 2rem;
        color: #000000;
        margin-bottom: 1rem;
    }

    .no-jobs p {
        font-size: 1.1rem;
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }

    .filter-section {
        display: flex;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }

    .filter-btn {
        padding: 10px 20px;
        background: #ffffff;
        color: #1877F2;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: none;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: #1877F2;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3);
    }

    @media (max-width: 768px) {
        .careers-hero h1 {
            font-size: 2.5rem;
        }

        .careers-hero p {
            font-size: 1rem;
        }

        .jobs-grid {
            grid-template-columns: 1fr;
        }

        .job-footer {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
<!-- Careers Hero Section -->
<section class="careers-hero">
    <div class="container">
        <h1>Join Our Team</h1>
        <p>Be part of a dynamic team driving digital transformation. Explore exciting career opportunities and grow with us.</p>
    </div>
</section>

<!-- Jobs Section -->
<section class="jobs-section">
    <div class="container">
        @if(isset($jobs) && count($jobs) > 0)
            <!-- Filter Buttons -->
            <div class="filter-section">
                <button class="filter-btn active" onclick="filterJobs('all')">All Positions</button>
                <button class="filter-btn" onclick="filterJobs('full-time')">Full-Time</button>
                <button class="filter-btn" onclick="filterJobs('part-time')">Part-Time</button>
                <button class="filter-btn" onclick="filterJobs('contract')">Contract</button>
                <button class="filter-btn" onclick="filterJobs('internship')">Internship</button>
            </div>

            <!-- Jobs Grid -->
            <div class="jobs-grid">
                @foreach($jobs as $job)
                <div class="job-card" data-job-type="{{ $job->job_type }}">
                    <span class="job-badge">{{ ucfirst(str_replace('-', ' ', $job->job_type)) }}</span>
                    <h2 class="job-title">{{ $job->title }}</h2>

                    <div class="job-meta">
                        <div class="job-meta-item">
                            <i class="fas fa-building"></i>
                            <span>{{ $job->department }}</span>
                        </div>
                        <div class="job-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $job->location }}</span>
                        </div>
                        @if(!empty($job->salary_range))
                        <div class="job-meta-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>{{ $job->salary_range }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="job-description">
                        {{ Str::limit(strip_tags($job->description), 150) }}
                    </div>

                    <div class="job-footer">
                        <div class="job-deadline">
                            <i class="fas fa-clock"></i>
                            Apply by: {{ $job->application_deadline->format('M d, Y') }}
                        </div>
                        <a href="{{ route('careers.show', $job->id) }}" class="apply-btn">View Details</a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="no-jobs">
                <h3>No Open Positions</h3>
                <p>We don't have any open positions at the moment, but we're always looking for talented individuals. Check back soon or connect with us on social media to stay updated!</p>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
function filterJobs(type) {
    const cards = document.querySelectorAll('.job-card');
    const buttons = document.querySelectorAll('.filter-btn');

    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    // Filter cards
    cards.forEach(card => {
        if (type === 'all' || card.dataset.jobType === type) {
            card.style.display = 'block';
            card.style.animation = 'fadeIn 0.5s ease-in';
        } else {
            card.style.display = 'none';
        }
    });
}

// Add fadeIn animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);
</script>
@endpush
