@extends('layouts.public')

@section('title', 'About Us | TeamO Digital Solutions')
@section('description', 'Learn about TeamO Digital Solutions, our mission, vision, and the talented team driving innovative solutions in digital services.')
@section('keywords', 'TeamO Digital Solutions, About, Mission, Vision, Team, Digital Services')
@section('og_title', 'About Us | TeamO Digital Solutions')
@section('og_description', 'Discover our mission, vision, and the talented team at TeamO Digital Solutions.')

@push('styles')
<style>
    /* Hero Section */
    .hero {
        background: white;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        z-index: 2;
        overflow: hidden;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(24, 119, 242, 0.1), rgba(0, 0, 0, 0.05));
    }

    .hero .container {
        position: relative;
        z-index: 1;
    }

    .hero h1 {
        background: linear-gradient(135deg, #1877F2, #1f2937, #1877F2);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 3.5rem;
        font-weight: bold;
        text-shadow: none;
    }

    .hero p {
        color: #374151;
        font-size: 1.2rem;
    }

    /* Content Sections */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: center;
        margin: 60px 0;
    }

    .responsive-img {
        width: 100%;
        max-width: 500px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(24, 119, 242, 0.15);
        transition: transform 0.3s ease-in-out;
    }

    .responsive-img:hover {
        transform: scale(1.05);
    }

    .section-alt {
        background: white;
        padding: 80px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .section-alt::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(24, 119, 242, 0.08), rgba(0, 0, 0, 0.03));
    }

    .section-alt .container {
        position: relative;
        z-index: 1;
    }

    .section-alt h2 {
        background: linear-gradient(135deg, #1877F2, #1f2937);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .section-alt p {
        color: #374151;
    }

    .section-alt .grid div {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 15px;
        border: none;
    }

    .section-alt .grid div .bg-white\/20 {
        background: linear-gradient(135deg, rgba(24, 119, 242, 0.2), rgba(0, 0, 0, 0.1)) !important;
        color: #1877F2;
    }

    .section-alt .grid div h3 {
        color: #1f2937;
    }

    .section-alt .grid div p {
        color: #6b7280;
    }

    /* Team Section */
    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 40px;
        max-width: 1000px;
        margin-left: auto;
        margin-right: auto;
    }

    .team-card {
        background: white;
        padding: 30px 20px;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(24, 119, 242, 0.1);
        text-align: center;
        transition: all 0.3s ease-in-out;
        position: relative;
        overflow: hidden;
        border: none;
    }

    .team-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 0px;
        background: none;
    }

    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(24, 119, 242, 0.2);
    }

    .team-card img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: none;
        margin-bottom: 20px;
        object-fit: cover;
        transition: transform 0.3s ease;
        box-shadow: 0 4px 15px rgba(24, 119, 242, 0.15);
    }

    .team-card:hover img {
        transform: scale(1.1);
    }

    .team-card h3 {
        font-size: 22px;
        margin: 15px 0 8px;
        font-weight: 600;
        color: #1f2937;
    }

    .team-card p {
        font-size: 16px;
        color: #6b7280;
        font-weight: 500;
    }

    .team-card a {
        color: #1877F2;
    }

    .team-card a:hover {
        color: #1f2937;
    }

    /* CTA Section */
    .cta-section {
        background: white;
        padding: 60px 20px;
        text-align: center;
        border-radius: 20px;
        margin: 60px 0;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(24, 119, 242, 0.05), rgba(0, 0, 0, 0.02));
    }

    .cta-section > * {
        position: relative;
        z-index: 1;
    }

    .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 30px;
    }

    .btn-secondary {
        display: inline-block;
        padding: 12px 24px;
        background: linear-gradient(135deg, #6b7280, #1f2937);
        color: #fff;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #4b5563, #111827);
        transform: translateY(-2px);
    }

    /* Values Section */
    .bg-gray-50 {
        background: white !important;
    }

    .bg-white.rounded-lg.shadow-md {
        background: white;
        border: none;
        box-shadow: 0 4px 15px rgba(24, 119, 242, 0.08);
    }

    .bg-blue-100 { background: linear-gradient(135deg, rgba(24, 119, 242, 0.2), rgba(0, 0, 0, 0.1)) !important; }
    .text-blue-600 { color: #1877F2 !important; }
    
    .bg-green-100 { background: linear-gradient(135deg, rgba(24, 119, 242, 0.15), rgba(0, 0, 0, 0.08)) !important; }
    .text-green-600 { color: #1f2937 !important; }
    
    .bg-purple-100 { background: linear-gradient(135deg, rgba(24, 119, 242, 0.18), rgba(0, 0, 0, 0.07)) !important; }
    .text-purple-600 { color: #1877F2 !important; }
    
    .bg-orange-100 { background: linear-gradient(135deg, rgba(24, 119, 242, 0.12), rgba(0, 0, 0, 0.05)) !important; }
    .text-orange-600 { color: #1f2937 !important; }

    /* Section titles */
    .section-title {
        background: linear-gradient(135deg, #1877F2, #1f2937);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .section-text {
        color: #374151;
        line-height: 1.6;
    }

    /* Button styles */
    .btn-primary {
        background: linear-gradient(135deg, #1877F2, #1f2937);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1d4ed8, #111827);
        transform: translateY(-2px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .content-grid {
            grid-template-columns: 1fr;
            gap: 30px;
            text-align: center;
        }

        .hero h1 {
            font-size: 2.5rem;
        }

        .team-grid {
            grid-template-columns: 1fr;
            max-width: 400px;
        }

        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
    }

    /* Animation for scroll reveal */
    .fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<header class="hero">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-6xl font-bold">About Us</h1>
        <p class="text-xl mt-4">Driving Innovation Through Technology</p>
    </div>
</header>

<!-- Who We Are Section -->
<section class="container mx-auto px-4 py-16" style="background: white;">
    <div class="content-grid fade-in">
        <div>
            <h2 class="section-title">Who We Are</h2>
            <p class="section-text mb-6">
                TEAMO Digital Solutions (TDS) is a trusted provider of records
                digitalisation and IT services, supporting businesses with secure
                document archiving, customised software, and end-to-end
                infrastructure support. We help organisations cut down on
                paperwork, improve efficiency, and strengthen data security.
            </p>
            <p class="section-text mb-8">
                Our approach is built around your needs. We listen, assess, and
                design solutions that address specific challenges. By combining
                technical expertise with practical experience, we make records
                easier to access, systems more reliable, and operations more
                efficient.
            </p>
            <a href="{{ route('contact') }}" class="btn-primary">Get In Touch</a>
        </div>
        <div class="text-center">
            <img src="{{ asset('images/team/TeamO.jpg') }}" alt="Our Team at Work" class="responsive-img">
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="section-alt">
    <div class="container mx-auto px-4">
        <h2 class="section-title mb-6">Our Mission</h2>
        <p class="text-xl leading-relaxed max-w-4xl mx-auto section-text">
            Our mission is to drive growth and transformation by harnessing the power of technology, collaboration, and a customer-centric approach. We strive to create value through sustainable and innovative solutions that empower businesses to thrive in the digital age.
        </p>
        <div class="grid md:grid-cols-3 gap-8 mt-12 max-w-4xl mx-auto">
            <div class="text-center">
                <div class="bg-white/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lightbulb text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Innovation</h3>
                <p>Cutting-edge solutions for modern challenges</p>
            </div>
            <div class="text-center">
                <div class="bg-white/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Collaboration</h3>
                <p>Working together to achieve excellence</p>
            </div>
            <div class="text-center">
                <div class="bg-white/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Customer Focus</h3>
                <p>Your success is our priority</p>
            </div>
        </div>
    </div>
</section>

<!-- Meet Our Team Section -->
<section class="container mx-auto px-4 py-16" style="background: white;">
    <div class="text-center mb-12 fade-in">
        <h2 class="section-title">Meet Our Team</h2>
        <p class="section-text max-w-2xl mx-auto">
            Our diverse team of experts brings together years of experience and a passion for innovation to deliver exceptional results for our clients.
        </p>
    </div>
    
    <!-- Grid: 1 col on small, 2 on medium, 4 on large -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 fade-in">
        
        <!-- CEO & Founder -->
        <div class="team-card text-center">
            <img src="/images/team/ceo.jpg" alt="CEO & Founder" class="mx-auto rounded-full w-40 h-40 object-cover">
            <h3 class="mt-4 font-semibold text-lg">Member Ojebode</h3>
            <p class="text-gray-600">CEO & Founder</p>
            <div class="mt-4">
            </div>
        </div>
        
        <!-- Head of Operations -->
        <div class="team-card text-center">
            <img src="/images/team/manager.jpg" alt="COO" class="mx-auto rounded-full w-40 h-40 object-cover">
            <h3 class="mt-4 font-semibold text-lg">Daniel Adeola</h3>
            <p class="text-gray-600">COO</p>
            <div class="mt-4">
            </div>
        </div>
        
        <!-- Another Team Member -->
        <div class="team-card text-center">
            <img src="/images/team/accountant.jpg" alt="Accountant" class="mx-auto rounded-full w-40 h-40 object-cover">
            <h3 class="mt-4 font-semibold text-lg">Sewuese Tivzua</h3>
            <p class="text-gray-600">Accountant</p>
            <div class="mt-4">
            </div>
        </div>
        
       
        <!-- Another Team Member -->
        <div class="team-card text-center">
            <img src="/images/team/hr.jpg" alt="HR" class="mx-auto rounded-full w-40 h-40 object-cover">
            <h3 class="mt-4 font-semibold text-lg">Mbangohol Ikeseh</h3>
            <p class="text-gray-600">HR</p>
            <div class="mt-4">
            </div>
        </div>
        
        <!-- Another Team Member -->
        <div class="team-card text-center">
            <img src="/images/team/member1.jpg" alt="Team Member" class="mx-auto rounded-full w-40 h-40 object-cover">
            <h3 class="mt-4 font-semibold text-lg">Eunice Kegh</h3>
            <p class="text-gray-600">Team Member</p>
            <div class="mt-4">
            </div>
        </div>
        
        <!-- Another Team Member -->
        <div class="team-card text-center">
            <img src="/images/team/member2.jpg" alt="Team Member" class="mx-auto rounded-full w-40 h-40 object-cover">
            <h3 class="mt-4 font-semibold text-lg">Ngumbughun Ikeseh</h3>
            <p class="text-gray-600">Team Member</p>
            <div class="mt-4">
            </div>
        </div>
        
      
        
         <!-- Another Team Member -->
        <div class="team-card text-center">
            <img src="/images/team/member3.jpg" alt="Team Member" class="mx-auto rounded-full w-40 h-40 object-cover">
            <h3 class="mt-4 font-semibold text-lg">Terungwa Emmanuel</h3>
            <p class="text-gray-600">Team Member</p>
            <div class="mt-4">
            </div>
        </div>
        
        
        <!-- Marketing Director -->
        <div class="team-card text-center">
            <img src="/images/team/member4.jpg" alt="Team Member" class="mx-auto rounded-full w-40 h-40 object-cover">
            <h3 class="mt-4 font-semibold text-lg">Charles Ikyese</h3>
            <p class="text-gray-600">Team Member</p>
            <div class="mt-4">
            </div>
        </div>
        
    </div>
</section>


<!-- Values Section -->
<section class="bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 fade-in">
            <h2 class="section-title">Our Values</h2>
            <p class="section-text max-w-2xl mx-auto">
                These core values guide everything we do and shape our relationships with clients, partners, and each other.
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
            <div class="text-center p-6 bg-white rounded-lg shadow-md fade-in">
                <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl #1877F2"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Integrity</h3>
                <p class="text-gray-600">We operate transparently and responsibly, building long-termpartnerships based on trust.</p>
            </div>
            
            <div class="text-center p-6 bg-white rounded-lg shadow-md fade-in">
                <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-rocket text-2xl text-green-600"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Excellence</h3>
                <p class="text-gray-600">We deliver every project with precision, care, and uncompromising
quality.</p>
            </div>
            
            <div class="text-center p-6 bg-white rounded-lg shadow-md fade-in">
                <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-handshake text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Dedication</h3>
                <p class="text-gray-600">Every project receives focused attention and support from start to
finish.</p>
            </div>
            
            <div class="text-center p-6 bg-white rounded-lg shadow-md fade-in">
                <div class="bg-orange-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cogs text-2xl text-orange-600"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Security</h3>
                <p class="text-gray-600">Your data is handled with strict confidentiality and protection
standards</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="container mx-auto px-4 py-16" style="background: white;">
    <div class="cta-section fade-in">
        <h2 class="section-title">Ready to Work With Us?</h2>
        <p class="section-text max-w-2xl mx-auto mb-8">
            Let's discuss how we can help transform your business with our innovative digital solutions.
        </p>
        <div class="cta-buttons">
            <a href="{{ route('contact') }}" class="btn-primary">Start a Project</a>
            <a href="{{ route('services') }}" class="btn-secondary">View Our Services</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Scroll reveal animation
    document.addEventListener('DOMContentLoaded', function() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });
    });
</script>
@endpush
