<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barangay Aliaga | IESMS</title>
    <link rel="icon" href="{{ $global_logo ? asset('storage/' . $global_logo) : asset('favicon.ico') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#16a34a', 
                        'primary-foreground': '#ffffff',
                    }
                }
            }
        }
    </script>

    <script src="https://unpkg.com/lucide@latest"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Smooth transition for the slider */
        .slider-wrapper {
            transition: transform 0.5s ease-in-out;
        }
        /* Hide scrollbar for cleaner look */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="antialiased min-h-screen bg-gradient-to-br from-green-50 to-yellow-50 text-slate-800 font-sans">

    <header class="bg-primary shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                
                <div class="flex items-center space-x-3 md:space-x-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full overflow-hidden bg-white border-2 border-green-400 shrink-0">
                        @if($global_logo ?? false) 
                            <img src="{{ asset('storage/' . $global_logo) }}" alt="Logo" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-green-200">
                                <i data-lucide="image" class="w-6 h-6"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h1 class="font-bold text-white leading-tight text-xl">Barangay {{ $global_brgy_name ?? 'Portal' }}</h1>
                        <p class="text-[11px] uppercase tracking-wider text-green-100 font-semibold">IESMS Portal</p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-white text-primary rounded-md text-sm font-medium hover:bg-green-50 transition shadow-sm">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-white hover:text-green-100 transition px-3 py-2">Log in</a>
                            <a href="{{ route('register') }}" class="sm:inline-flex px-4 py-2 bg-white text-primary rounded-md text-sm font-medium hover:bg-green-50 transition shadow-sm">Register</a>
                        @endauth
                    @endif
                </div>
                
            </div>
        </div>
    </header>

    <section class="relative py-16 sm:py-24 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left z-10">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6 text-gray-900 tracking-tight leading-tight">
                        Integrated E-Services <span class="text-primary">Management System</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-600 mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        "Pagkakaisa sa Paglilingkod, Progreso sa Pamayanan" — Your gateway to efficient, transparent, and accessible barangay services.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="/login" class="inline-flex justify-center items-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-green-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Access Services
                            <i data-lucide="arrow-right" class="ml-2 w-5 h-5"></i>
                        </a>
                        <a href="#announcements" class="inline-flex justify-center items-center px-8 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                            View News
                        </a>
                    </div>
                </div>
                <div class="relative w-full h-[300px] sm:h-[400px] lg:h-[450px] rounded-2xl shadow-2xl overflow-hidden border-4 border-white">
                    <iframe 
                        class="absolute inset-0 w-full h-full"
                        frameborder="0" 
                        scrolling="no" 
                        marginheight="0" 
                        marginwidth="0" 
                        title="Map of Barangay Aliaga"
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d608.6048259025364!2d120.44783652260098!3d15.889278660353389!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3391466ff7723a6f%3A0x69cefdf54c2f2b22!2sBrgy%20Aliaga%20Multi-Purpose%20Hall!5e1!3m2!1sen!2sus!4v1771690839393!5m2!1sen!2sus"
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white relative">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <span class="text-primary font-semibold tracking-wider uppercase text-sm">What we offer</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-2">Available Services</h2>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($services as $service)
                <div class="group bg-white rounded-xl border border-gray-100 shadow-sm p-8 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="w-14 h-14 bg-green-50 rounded-xl flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                        <i data-lucide="{{ $service['icon'] }}" class="w-7 h-7"></i>
                    </div>
                    <h3 class="mb-3 text-lg font-bold text-gray-900">{{ $service['title'] }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $service['description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-green-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">How does it work?</h2>
                <p class="text-lg text-gray-600">Get your requested documents in 3 simple steps</p>
            </div>
            
            <div class="relative">
                <div class="hidden md:block absolute top-12 left-1/6 right-1/6 h-0.5 bg-green-200 z-0 transform -translate-y-1/2"></div>

                <div class="grid md:grid-cols-3 gap-12 relative z-10">
                    @foreach($howItWorksSteps as $index => $step)
                    <div class="flex flex-col items-center text-center">
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg mb-6 border-4 border-white relative">
                            <span class="text-3xl font-bold text-primary">{{ $index + 1 }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $step['title'] }}</h3>
                        <p class="text-gray-600 leading-relaxed max-w-xs">{{ $step['description'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="announcements" class="py-20 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Recent Announcements</h2>
                    <p class="text-gray-500 mt-2">News and updates from the Barangay Hall</p>
                </div>
                {{-- Slider Controls --}}
                <div class="flex space-x-2 mt-4 md:mt-0">
                    <button onclick="moveSlide(-1)" class="p-2 rounded-full border hover:bg-gray-50 text-gray-600 transition">
                        <i data-lucide="chevron-left" class="w-6 h-6"></i>
                    </button>
                    <button onclick="moveSlide(1)" class="p-2 rounded-full border hover:bg-gray-50 text-gray-600 transition">
                        <i data-lucide="chevron-right" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            @if($announcements && $announcements->count() > 0)
                <div class="relative group" onmouseenter="pauseSlider()" onmouseleave="resumeSlider()">
                    <div class="overflow-hidden rounded-2xl shadow-xl border border-gray-100 bg-white">
                        <div id="slider-container" class="flex slider-wrapper w-full">
                            
                            @foreach($announcements as $announcement)
                            <div class="w-full flex-shrink-0 min-w-full">
                                <div class="flex flex-col md:flex-row h-full md:h-[400px]">
                                    
                                    {{-- Image Section --}}
                                    <div class="md:w-5/12 relative bg-gray-200 h-64 md:h-full">
                                        @php
                                            $announceImg = $announcement->cover_image 
                                                ? Storage::url($announcement->cover_image) 
                                                : 'https://placehold.co/600x400/e2e8f0/1e293b?text=Announcement';
                                        @endphp
                                        <img src="{{ $announceImg }}" alt="{{ $announcement->title }}" class="w-full h-full object-cover">
                                        
                                        {{-- Optional: Status Badge if needed --}}
                                        @if($announcement->status === 'archived')
                                            <div class="absolute top-4 left-4">
                                                <span class="bg-gray-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md uppercase tracking-wide">Archived</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Content Section --}}
                                    <div class="md:w-7/12 p-8 md:p-10 flex flex-col justify-between bg-white">
                                        <div>
                                            <div class="flex items-center space-x-4 mb-6">
                                                @php 
                                                    // Use publish_at, fallback to created_at
                                                    $date = $announcement->publish_at ?? $announcement->created_at;
                                                @endphp
                                                
                                                <div class="flex flex-col items-center justify-center bg-green-50 text-green-700 rounded-lg px-4 py-2 border border-green-100">
                                                    <span class="text-2xl font-bold leading-none">{{ $date->format('d') }}</span>
                                                    <span class="text-xs font-bold uppercase">{{ $date->format('M') }}</span>
                                                </div>
                                                <div class="text-sm text-gray-400 flex items-center">
                                                    <i data-lucide="clock" class="w-4 h-4 mr-1"></i>
                                                    {{ $date->format('h:i A') }}
                                                </div>
                                            </div>

                                            <h3 class="text-2xl font-bold text-gray-900 mb-4 line-clamp-2 leading-tight capitalize">
                                                {{ $announcement->title }}
                                            </h3>
                                            
                                            <div class="text-gray-600 leading-relaxed mb-6 line-clamp-3">
                                                {!! Str::limit(strip_tags($announcement->content), 250) !!}
                                            </div>
                                        </div>

                                        <div>
                                            {{-- Link uses the object for automatic slug resolution --}}
                                            <a href="{{ route('public.announcements.show', $announcement) }}" class="text-green-600 hover:text-green-800 font-semibold text-sm flex items-center group/btn">
                                                Read Full Announcement 
                                                <i data-lucide="arrow-right" class="w-4 h-4 ml-2 transform group-hover/btn:translate-x-1 transition-transform"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-12 text-center border-2 border-dashed border-gray-200">
                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                    <p class="text-gray-500 font-medium">No active announcements at the moment.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="py-16 bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Barangay Officials</h2>
                <p class="text-gray-600">Meet the leaders serving our community</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @forelse($officials as $official)
                    @php
                        // Safely grab the current term from the loaded terms
                        $activeTerm = $official->terms->where('status', 'current')->first();
                    @endphp
                    
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="aspect-[4/5] overflow-hidden bg-gray-200 relative">
                            @php
                                // Safely navigate the Resident -> User relationship
                                $userPhoto = $official->resident?->user?->profile_photo;
                                // Check if resident has a direct image (if your DB has this column)
                                $residentImage = $official->user?->profile_photo ?? null; 
                                
                                $imagePath = $userPhoto ?? $residentImage;
                                
                                if ($imagePath) {
                                    $src = asset('storage/' . $imagePath);
                                } else {
                                    // Uses your getFullNameAttribute() beautifully!
                                    $name = $official->resident?->full_name ?? 'Official';
                                    $src = 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=16a34a&color=fff&size=512';
                                }
                            @endphp
                            
                            <img src="{{ $src }}" alt="Official" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-60"></div>
                            
                            <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                                <p class="text-xs font-semibold uppercase tracking-wider bg-primary/90 inline-block px-2 py-1 rounded mb-1">
                                    {{ $activeTerm?->position?->title ?? 'Official' }}
                                </p>
                            </div>
                        </div>
                        <div class="p-5">
                            {{-- Safely utilizing the getFullNameAttribute() --}}
                            <h3 class="font-bold text-gray-900 text-lg leading-tight capitalize">{{ $official->resident?->full_name ?? 'Unknown' }}</h3>
                            
                            {{-- Added Phone Number Display --}}
                            @if($official->resident?->phone_number)
                                <div class="mt-2 flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    {{ $official->resident->phone_number }}
                                </div>
                            @else
                                <div class="mt-2 flex items-center text-sm text-gray-400 italic">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    No contact number
                                </div>
                            @endif

                            <div class="mt-3 flex space-x-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                {{-- You can put social media links or action buttons here in the future --}}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-10">
                        <p class="text-gray-500 italic">Official list is currently being updated.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-16 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Contact Us</h2>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="bg-green-50 p-3 rounded-lg text-primary">
                                <i data-lucide="map-pin" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Visit our Office</p>
                                <p class="text-gray-600">{{ $settings['address'] ?? 'Aliaga, Malasiqui Pangasinan' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="bg-green-50 p-3 rounded-lg text-primary">
                                <i data-lucide="phone" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Call Us</p>
                                <p class="text-gray-600">{{ $settings['contact_phone'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="bg-green-50 p-3 rounded-lg text-primary">
                                <i data-lucide="mail" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Email Us</p>
                                <p class="text-gray-600">{{ $settings['office_email'] ?? 'info@barangayaliaga.gov.ph' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Office Hours</h3>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center border-b border-gray-200 pb-2">
                            <span class="text-gray-600">Monday - Friday</span>
                            <span class="font-semibold text-primary">8:00 AM - 5:00 PM</span>
                        </li>
                        <li class="flex justify-between items-center border-b border-gray-200 pb-2">
                            <span class="text-gray-600">Saturday</span>
                            <span class="font-semibold text-primary">8:00 AM - 12:00 PM</span>
                        </li>
                        <li class="flex justify-between items-center text-gray-400">
                            <span>Sunday</span>
                            <span>Closed</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <script>
        // Initialize Icons
        lucide.createIcons();

        // SLIDER LOGIC
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('slider-container');
            if(!container) return; // Exit if no announcements

            const slides = container.children;
            const totalSlides = slides.length;
            let currentSlide = 0;
            let autoPlayInterval;

            // Make moveSlide global so buttons can access it
            window.moveSlide = function(direction) {
                currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
                updateCarousel();
                resetTimer(); // Reset auto-play timer on manual click
            }

            // Function to update CSS transform
            function updateCarousel() {
                container.style.transform = `translateX(-${currentSlide * 100}%)`;
            }

            // Auto Play Functionality
            function startAutoPlay() {
                autoPlayInterval = setInterval(() => {
                    moveSlide(1);
                }, 5000); // Change slide every 5 seconds
            }

            function stopAutoPlay() {
                clearInterval(autoPlayInterval);
            }

            // Global functions for hover pause (called in HTML)
            window.pauseSlider = stopAutoPlay;
            window.resumeSlider = startAutoPlay;

            // Helper to reset timer on manual interaction
            function resetTimer() {
                stopAutoPlay();
                startAutoPlay();
            }

            // Start on load
            if(totalSlides > 1) {
                startAutoPlay();
            }
        });
    </script>

    @include('partials.chatbot')
</body>
</html>