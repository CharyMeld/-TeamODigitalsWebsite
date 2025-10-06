@extends('layouts.public')

@section('title', $page_title ?? 'Job Details - TeamO Digital Solutions')
@section('description', $meta_description ?? 'Apply for this position at TeamO Digital Solutions')
@section('keywords', 'job application, careers, employment, TeamO Digital Solutions')

@push('styles')
<style>
    .job-detail-hero {
        background: linear-gradient(135deg, rgba(24, 119, 242, 0.1), transparent);
        padding: 60px 0 40px;
    }

    .job-detail-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .job-header {
        background: #ffffff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .job-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #000000;
        margin-bottom: 20px;
    }

    .job-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #666;
    }

    .meta-item i {
        color: #1877F2;
        font-size: 1.2rem;
    }

    .job-content {
        background: #ffffff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .job-section {
        margin-bottom: 30px;
    }

    .job-section h2 {
        font-size: 1.8rem;
        font-weight: 600;
        color: #000000;
        margin-bottom: 15px;
        border-bottom: 3px solid #1877F2;
        padding-bottom: 10px;
    }

    .job-section p, .job-section ul {
        color: #333;
        line-height: 1.8;
    }

    .job-section ul {
        list-style: none;
        padding: 0;
    }

    .job-section ul li {
        padding: 8px 0;
        padding-left: 25px;
        position: relative;
    }

    .job-section ul li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #1877F2;
        font-weight: bold;
    }

    .apply-section {
        background: linear-gradient(135deg, #1877F2, #0056b3);
        padding: 40px;
        border-radius: 15px;
        text-align: center;
        color: #ffffff;
        margin-top: 40px;
    }

    .apply-section h3 {
        font-size: 2rem;
        margin-bottom: 15px;
        color: #ffffff;
    }

    .apply-btn {
        background: #ffffff;
        color: #1877F2;
        padding: 15px 40px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        display: inline-block;
        margin-top: 15px;
        transition: all 0.3s ease;
    }

    .apply-btn:hover {
        background: #000000;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }

    .deadline-badge {
        display: inline-block;
        background: #ff6b6b;
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin-top: 15px;
    }

    @media (max-width: 768px) {
        .job-title {
            font-size: 2rem;
        }

        .job-header, .job-content {
            padding: 25px;
        }
    }
</style>
@endpush

@section('content')
<div class="job-detail-hero">
    <div class="job-detail-container">
        <div class="job-header">
            <h1 class="job-title">{{ $job->title }}</h1>

            <div class="job-meta-grid">
                <div class="meta-item">
                    <i class="fas fa-building"></i>
                    <span><strong>Department:</strong> {{ $job->department }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><strong>Location:</strong> {{ $job->location }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-briefcase"></i>
                    <span><strong>Type:</strong> {{ ucfirst(str_replace('-', ' ', $job->job_type)) }}</span>
                </div>
                @if($job->salary_range)
                <div class="meta-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <span><strong>Salary:</strong> {{ $job->salary_range }}</span>
                </div>
                @endif
            </div>

            <div class="deadline-badge">
                <i class="fas fa-clock"></i> Apply by: {{ $job->application_deadline->format('M d, Y') }}
            </div>
        </div>

        <div class="job-content">
            <div class="job-section">
                <h2>Job Description</h2>
                <p>{!! nl2br(e($job->description)) !!}</p>
            </div>

            <div class="job-section">
                <h2>Requirements</h2>
                <ul>
                    @foreach(explode("\n", $job->requirements) as $requirement)
                        @if(trim($requirement))
                        <li>{{ trim($requirement) }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>

            @if($job->responsibilities)
            <div class="job-section">
                <h2>Responsibilities</h2>
                <ul>
                    @foreach(explode("\n", $job->responsibilities) as $responsibility)
                        @if(trim($responsibility))
                        <li>{{ trim($responsibility) }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div class="apply-section">
            <h3>Ready to Join Our Team?</h3>
            <p>Submit your application and take the next step in your career journey</p>
            <a href="{{ route('careers.apply', $job->id) }}" class="apply-btn">
                <i class="fas fa-paper-plane"></i> Apply Now
            </a>
        </div>
    </div>
</div>
@endsection
