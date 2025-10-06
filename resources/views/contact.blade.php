@extends('layouts.public')

@section('title', 'Contact Us | TeamO Digital Solutions')
@section('description', 'Get in touch with TeamO Digital Solutions. Contact us for web development, IT consulting, and digital transformation services.')
@section('keywords', 'TeamO Digital Solutions, Contact, Web Development, IT Consulting, Digital Services, Nigeria')

@push('styles')
<style>
    body {
        background-color: #ffffff;
    }

    .contact-hero {
        background: #ffffff;
        padding: 60px 0;
        text-align: center;
        color: #1e40af;
        position: relative;
        overflow: hidden;
        border-bottom: none;
    }

    .contact-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(147,197,253,0.1)"><polygon points="0,0 1000,0 1000,100 0,80"/></svg>') no-repeat bottom;
        background-size: cover;
    }

    .contact-hero .container {
        position: relative;
        z-index: 1;
    }

    .contact-section {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        padding: 60px 40px;
        background: #ffffff;
        max-width: 1200px;
        margin: -40px auto 60px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(147, 197, 253, 0.2);
        position: relative;
        z-index: 10;
        border: none;
    }

    .contact-info, .contact-form {
        flex: 1;
        padding: 20px;
        min-width: 300px;
    }

    .contact-info {
        color: #1e40af;
    }

    .contact-info h2 {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        font-weight: 700;
        color: #000000;
    }

    .contact-info p {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        line-height: 1.6;
        color: #1e40af;
    }

    .contact-info .info-item {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
        padding: 1rem;
        background: #f0f9ff;
        border-radius: 10px;
        border: none;
    }

    .contact-info .info-item i {
        font-size: 1.5rem;
        margin-right: 1rem;
        color: #1e40af;
        width: 30px;
    }

    .contact-info .info-item div {
        color: #000000;
    }

    .contact-info .info-item strong {
        color: #1e40af;
    }

    .contact-info .info-item a {
        color: #1e40af;
        text-decoration: none;
    }

    .contact-info .info-item a:hover {
        color: #000000;
    }

    .contact-form {
        background: #ffffff;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(147, 197, 253, 0.2);
        border: none;
    }

    .contact-form h3 {
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
        color: #000000;
        text-align: center;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .contact-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #000000;
    }

    .contact-form input, 
    .contact-form textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #93c5fd;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: #ffffff;
        color: #000000;
    }

    .contact-form input:focus, 
    .contact-form textarea:focus {
        outline: none;
        border-color: #1e40af;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    .contact-form textarea {
        resize: vertical;
        min-height: 120px;
    }

    .contact-form button {
        width: 100%;
        padding: 14px 20px;
        background: #1e40af;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .contact-form button:hover {
        background: #000000;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3);
    }

    .contact-form button:active {
        transform: translateY(0);
    }

    .form-message {
        margin-top: 1rem;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        font-weight: 500;
    }

    .form-message.success {
        background: #f0f9ff;
        color: #1e40af;
        border: none;
    }

    .form-message.error {
        background: #ffffff;
        color: #000000;
        border: none;
    }

    .map-container {
        text-align: center;
        max-width: 1200px;
        margin: 0 auto 60px;
        padding: 0 20px;
        background: #ffffff;
    }

    .map-container h3 {
        font-size: 2rem;
        margin-bottom: 2rem;
        color: #000000;
        font-weight: 600;
    }

    .map-container iframe {
        width: 100%;
        height: 450px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(147, 197, 253, 0.2);
        border: none;
    }

    /* Loading animation */
    .loading {
        display: none;
        width: 20px;
        height: 20px;
        border: 2px solid #ffffff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .contact-form button.loading .loading {
        display: inline-block;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .contact-section {
            flex-direction: column;
            margin: -20px 20px 40px;
            padding: 40px 20px;
        }

        .contact-info, .contact-form {
            min-width: 100%;
            padding: 20px 0;
        }

        .contact-form {
            padding: 30px 20px;
        }

        .contact-info h2 {
            font-size: 2rem;
        }

        .map-container iframe {
            height: 300px;
        }
    }

    /* Animation for form elements */
    .form-group {
        opacity: 0;
        transform: translateY(20px);
        animation: slideUp 0.6s ease forwards;
    }

    .form-group:nth-child(1) { animation-delay: 0.1s; }
    .form-group:nth-child(2) { animation-delay: 0.2s; }
    .form-group:nth-child(3) { animation-delay: 0.3s; }
    .form-group:nth-child(4) { animation-delay: 0.4s; }

    @keyframes slideUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* FAQ Section */
    .bg-white {
        background-color: #ffffff;
    }

    .text-gray-800 {
        color: #000000;
    }

    .text-gray-600 {
        color: #1e40af;
    }

    .border-gray-200 {
        border-color: #93c5fd;
    }

    /* Error span styling */
    .text-red-500 {
        color: #1e40af;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="contact-hero">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Get In Touch</h1>
        <p class="text-xl opacity-90">We'd love to hear from you and discuss your project</p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="contact-info">
        <h2>Let's Connect</h2>
        <p class="mb-8">Ready to transform your business with cutting-edge digital solutions? Our team is here to help you every step of the way.</p>
        
        <div class="info-item">
            <i class="bi bi-geo-alt"></i>
            <div>
                <strong>Main Office</strong><br>
                Plot 40, Rasco Close, Sasa<br>
                Ibadan, Oyo State, Nigeria
            </div>
        </div>
        
        <div class="info-item">
            <i class="bi bi-telephone"></i>
            <div>
                <strong>Phone</strong><br>
                <a href="tel:+2348144466160">+234 814 446 6160</a>
            </div>
        </div>
        
        <div class="info-item">
            <i class="bi bi-envelope"></i>
            <div>
                <strong>Email</strong><br>
                <a href="mailto:teamodigitalsolutions@gmail.com">teamodigitalsolutions@gmail.com</a>
            </div>
        </div>
        
        <div class="info-item">
            <i class="bi bi-clock"></i>
            <div>
                <strong>Business Hours</strong><br>
                Mon - Fri: 9:00 AM - 6:00 PM<br>
                Sat: 10:00 AM - 4:00 PM
            </div>
        </div>
    </div>
    
    <div class="contact-form">
        <h3>Send Us a Message</h3>
        <form id="contactForm" action="{{ route('contact.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required value="{{ old('name') }}">
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required value="{{ old('email') }}">
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                @error('phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}">
                @error('subject')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="message">Message *</label>
                <textarea id="message" name="message" rows="5" required placeholder="Tell us about your project or how we can help you...">{{ old('message') }}</textarea>
                @error('message')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit">
                <span class="loading"></span>
                <span class="button-text">Send Message</span>
            </button>
        </form>
        
        <div id="formMessage" class="form-message" style="display: none;"></div>
        
        @if(session('success'))
            <div class="form-message success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="form-message error">
                {{ session('error') }}
            </div>
        @endif
    </div>
</section>

<!-- Map Section -->
<section class="map-container">
    <h3>Find Us on the Map</h3>
    <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3963.84444939761!2d3.3559001735884575!3d6.541318522958921!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x103b8dc0a5214979%3A0x3e0706158e02a7db!2s15%20Oladiti%20Adebiyi%20St%2C%20Onipanu%2C%20Lagos%20102215%2C%20Lagos!5e0!3m2!1sen!2sng!4v1743455006580!5m2!1sen!2sng"
        allowfullscreen="" 
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</section>

<!-- FAQ Section -->
<section class="bg-white py-16">
    <div class="flex justify-center">
        <div class="w-full max-w-[1200px] px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Frequently Asked Questions</h2>
            
            <div class="space-y-6">
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">How quickly can you start my project?</h3>
                    <p class="text-gray-600">We typically begin new projects within 1-2 weeks of contract signing, depending on our current workload and project complexity.</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Do you provide ongoing support?</h3>
                    <p class="text-gray-600">Yes, we offer comprehensive maintenance and support packages to ensure your digital solutions continue to perform optimally.</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">What industries do you work with?</h3>
                    <p class="text-gray-600">We work with businesses across various industries including healthcare, finance, education, retail, and manufacturing.</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Can you work with our existing systems?</h3>
                    <p class="text-gray-600">Absolutely! We specialize in integrating new solutions with existing systems to ensure seamless operations.</p>
                </div>
            </div>
        </div>
    </div>
</section>



@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const messageDiv = document.getElementById('formMessage');
    const submitButton = form.querySelector('button[type="submit"]');
    const buttonText = submitButton.querySelector('.button-text');
    const loading = submitButton.querySelector('.loading');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitButton.classList.add('loading');
        submitButton.disabled = true;
        buttonText.textContent = 'Sending...';
        
        // Hide previous messages
        messageDiv.style.display = 'none';
        messageDiv.className = 'form-message';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.textContent = data.message || 'Message sent successfully!';
                messageDiv.classList.add('success');
                form.reset();
            } else {
                messageDiv.textContent = data.message || 'Failed to send message. Please try again.';
                messageDiv.classList.add('error');
            }
            messageDiv.style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.textContent = 'An error occurred. Please try again later.';
            messageDiv.classList.add('error');
            messageDiv.style.display = 'block';
        })
        .finally(() => {
            // Reset button state
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
            buttonText.textContent = 'Send Message';
        });
    });

    // Form validation
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.style.borderColor = '#1e40af';
            } else {
                this.style.borderColor = '#93c5fd';
            }
        });

        input.addEventListener('input', function() {
            if (this.style.borderColor === 'rgb(30, 64, 175)') {
                this.style.borderColor = '#93c5fd';
            }
        });
    });

    // Email validation
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            this.style.borderColor = '#1e40af';
        }
    });
});
</script>
@endpush
