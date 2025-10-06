@extends('layouts.public')

@section('title', 'Apply for ' . $job->title . ' - TeamO Digital Solutions')
@section('description', 'Submit your application for ' . $job->title . ' at TeamO Digital Solutions')

@push('styles')
<style>
    .apply-container {
        max-width: 800px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .apply-header {
        background: linear-gradient(135deg, #1877F2, #0056b3);
        color: #ffffff;
        padding: 40px;
        border-radius: 15px 15px 0 0;
        text-align: center;
    }

    .apply-header h1 {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #ffffff;
    }

    .apply-header p {
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }

    .apply-form {
        background: #ffffff;
        padding: 40px;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #000000;
        margin-bottom: 8px;
    }

    .form-group label .required {
        color: #ff6b6b;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #1877F2;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .file-upload {
        position: relative;
    }

    .file-upload input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .file-upload-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        border: 2px dashed #1877F2;
        border-radius: 8px;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-label:hover {
        background: #e3f2fd;
    }

    .file-upload-label i {
        color: #1877F2;
    }

    .submit-btn {
        background: #1877F2;
        color: #ffffff;
        padding: 15px 40px;
        border: none;
        border-radius: 30px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .submit-btn:hover {
        background: #000000;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .info-text {
        color: #666;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .apply-form {
            padding: 25px;
        }
    }
</style>
@endpush

@section('content')
<div class="apply-container">
    <div class="apply-header">
        <h1>Apply for {{ $job->title }}</h1>
        <p>{{ $job->department }} • {{ $job->location }}</p>
    </div>

    @if(session('success'))
    <div style="background: #4caf50; color: white; padding: 15px; border-radius: 8px; margin: 20px 0;">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background: #ff6b6b; color: white; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('careers.submit', $job->id) }}" method="POST" enctype="multipart/form-data" class="apply-form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required>
            </div>

            <div class="form-group">
                <label>Email Address <span class="required">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Phone Number <span class="required">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required>
            </div>

            <div class="form-group">
                <label>Years of Experience</label>
                <input type="number" name="years_of_experience" value="{{ old('years_of_experience') }}" min="0">
            </div>
        </div>

        <div class="form-group">
            <label>Resume/CV <span class="required">*</span></label>
            <div class="file-upload">
                <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx" required>
                <div class="file-upload-label">
                    <i class="fas fa-upload"></i>
                    <span id="file-name">Choose file (PDF, DOC, DOCX)</span>
                </div>
            </div>
            <p class="info-text">Maximum file size: 5MB</p>
        </div>

        <div class="form-group">
            <label>Cover Letter <span class="required">*</span></label>
            <textarea name="cover_letter" required>{{ old('cover_letter') }}</textarea>
            <p class="info-text">Tell us why you're a great fit for this role</p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>LinkedIn Profile URL</label>
                <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="https://linkedin.com/in/yourprofile">
            </div>

            <div class="form-group">
                <label>Portfolio/Website URL</label>
                <input type="url" name="portfolio_url" value="{{ old('portfolio_url') }}" placeholder="https://yourportfolio.com">
            </div>
        </div>

        <button type="submit" class="submit-btn">
            <i class="fas fa-paper-plane"></i> Submit Application
        </button>
    </form>
</div>

<script>
document.getElementById('resume').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Choose file (PDF, DOC, DOCX)';
    document.getElementById('file-name').textContent = fileName;
});
</script>
@endsection
