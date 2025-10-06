@extends('layouts.public')

@section('title', $page_title ?? 'Our Services - TeamO Digitals')
@section('meta_description', $meta_description ?? 'Explore our comprehensive digital services designed to transform your business.')
@section('canonical_url', $canonical_url ?? url()->current())
@section('og_title', $og_title ?? 'Our Services - TeamO Digitals')
@section('og_description', $og_description ?? 'Discover how TeamO Digitals helps businesses grow with expert digital services.')

@push('structured_data')
<script type="application/ld+json">
{!! json_encode($structured_data ?? [], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<div class="min-h-screen bg-white">

    <!-- Services Section -->
    <section class="py-24 px-6 bg-white">
        <div class="max-w-7xl mx-auto">

            <!-- Section Header -->
            <div class="text-center mb-20">
                <h2 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-gray-900 via-blue-600 to-black bg-clip-text text-transparent leading-tight">
                    Our <span class="bg-gradient-to-r from-blue-600 to-black/80 bg-clip-text text-transparent">Expert Services</span>
                </h2>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
                    Explore our range of transformative digital services designed to give your business a competitive edge.
                </p>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-7xl mx-auto">
                @foreach($services as $service)
                <div class="service-card bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">

                    <!-- Service Header with Icon -->
                    <div class="flex items-center gap-4 p-6 bg-gradient-to-r from-blue-50 to-white border-b border-gray-100">
                        <div class="w-16 h-16 flex items-center justify-center text-blue-600 rounded-lg">
                            <i class="{{ $service['icon'] }} text-white text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $service['title'] }}</h3>
                    </div>

                    <!-- Service Content -->
                    <div class="p-6 flex-1 flex flex-col">
                        <!-- Intro -->
                        <p class="text-gray-700 mb-4">{{ $service['intro'] }}</p>

                        <!-- Features (Records Digitalisation & Custom Software) -->
                        @if(isset($service['features']))
                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Features</h4>
                            <ul class="space-y-1 text-gray-600 text-sm">
                                @foreach($service['features'] as $feature)
                                <li class="flex items-start">
                                    <span class="text-blue-600 mr-2">●</span>
                                    <span>{{ $feature }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Benefits (Records Digitalisation & Custom Software) -->
                        @if(isset($service['benefits']))
                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-900 mb-2">What you get</h4>
                            <ul class="space-y-1 text-gray-600 text-sm">
                                @foreach($service['benefits'] as $benefit)
                                <li class="flex items-start">
                                    <span class="text-blue-600 mr-2">●</span>
                                    <span>{{ $benefit }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Services List (IT Support & Consultancy) -->
                        @if(isset($service['services']))
                        <div class="mb-4 space-y-3">
                            @foreach($service['services'] as $item)
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm">{{ $item['name'] }}</h4>
                                <p class="text-gray-600 text-sm">{{ $item['description'] }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Footer (Consultancy) -->
                        @if(isset($service['footer']))
                        <p class="text-gray-700 text-sm mb-4 italic">{{ $service['footer'] }}</p>
                        @endif

                        <!-- Note -->
                        @if(isset($service['note']) && $service['note'])
                        <p class="text-sm text-gray-600 italic mb-4"><strong>Note:</strong> {{ $service['note'] }}</p>
                        @endif

                        <!-- CTA Button -->
                        <div class="mt-auto pt-4">
                            <a href="{{ route('contact') }}"
                               class="block w-full text-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition">
                                {{ $service['cta_text'] }}
                            </a>
                            <p class="text-center text-sm text-gray-500 mt-2">{{ $service['cta_subtext'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-24 px-6 bg-white">
        <div class="bg-white rounded-3xl p-12 text-center shadow-2xl border border-blue-100/50 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-100/40 via-blue-50/30 to-black/10"></div>
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-r from-blue-200/30 to-black/15 rounded-full blur-2xl -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-r from-blue-300/25 to-black/20 rounded-full blur-xl -ml-16 -mb-16"></div>

            <div class="relative z-10">
                <h3 class="text-4xl font-extrabold mb-4 bg-gradient-to-r from-gray-900 via-blue-600 to-black bg-clip-text text-transparent">Ready to Transform Your Business?</h3>
                <p class="text-lg text-gray-700 mb-8 max-w-2xl mx-auto">
                    Let's collaborate and design innovative solutions that deliver measurable results.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" 
                       class="px-8 py-4 bg-gradient-to-r from-blue-600 via-blue-700 to-black/90 text-white font-semibold rounded-xl shadow-lg hover:from-blue-700 hover:via-black hover:to-blue-800 transition transform hover:scale-105">
                        Get Started Today
                    </a>
                    <a href="tel:+2348144466160" 
                       class="px-8 py-4 border-2 border-gray-300 bg-white text-gray-800 font-semibold rounded-xl hover:#1877F2 hover:#1877F2 hover:shadow-md transition transform hover:scale-105">
                        Call Us Now
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection



