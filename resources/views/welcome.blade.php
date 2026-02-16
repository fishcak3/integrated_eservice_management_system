<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barangay Aliaga | E-Services</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-800 dark:text-zinc-200 antialiased font-sans">

    {{-- 1. HEADER --}}
    <flux:header container class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 py-4 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div class="size-10 bg-blue-900 rounded-full flex items-center justify-center text-white font-bold text-xs">
                LOGO
            </div>
            <div class="leading-tight">
                <div class="font-bold text-lg tracking-tight text-zinc-900 dark:text-white uppercase">Barangay Aliaga</div>
                <div class="text-xs text-zinc-500 font-medium uppercase tracking-wider">E-Services Portal</div>
            </div>
        </div>

        <flux:spacer />

        <flux:navbar class="hidden md:flex gap-4">
            <flux:navbar.item href="#mission">Our Mission</flux:navbar.item>
            <flux:navbar.item href="#services">Services</flux:navbar.item>
            <flux:navbar.item href="#process">How It Works</flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <div class="flex gap-2">
            @if (Route::has('login'))
                @auth
                    {{-- Logic to redirect to specific dashboard based on role would be handled by the route/controller --}}
                    <flux:button href="{{ url('/dashboard') }}" variant="filled">
                        Dashboard
                    </flux:button>
                @else
                    <flux:button href="{{ route('login') }}" variant="ghost">Log In</flux:button>
                    @if (Route::has('register'))
                        <flux:button href="{{ route('register') }}" class="bg-blue-900 hover:bg-blue-800 text-white border-none">
                            Register
                        </flux:button>
                    @endif
                @endauth
            @endif
        </div>
    </flux:header>

    {{-- 2. HERO SECTION --}}
    <div class="relative bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
        <div class="absolute inset-0 bg-[url('https://fluxui.dev/img/grid.svg')] bg-center opacity-30"></div>
        
        <flux:main container class="relative py-24 text-center">
            <div class="mx-auto max-w-4xl space-y-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-xs font-semibold uppercase tracking-wider border border-blue-100 dark:border-blue-800">
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    System Online 24/7
                </div>

                <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Integrated E-Services <br> Management System
                </h1>
                
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed">
                    Efficient, transparent, and accessible government services for all residents.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                    <flux:button href="{{ route('login') }}" variant="primary" class="bg-blue-900 hover:bg-blue-800 border-none w-full sm:w-auto min-w-[160px] h-12 text-base">
                        Access Services
                    </flux:button>
                    <flux:button href="#mission" variant="outline" class="w-full sm:w-auto min-w-[160px] h-12 text-base">
                        Learn More
                    </flux:button>
                </div>
            </div>
        </flux:main>
    </div>

    {{-- 3. MISSION SECTION (From React Code) --}}
    <div id="mission" class="bg-zinc-50 dark:bg-zinc-950 py-20 border-b border-zinc-200 dark:border-zinc-800">
        <flux:main container>
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <flux:heading level="2" size="xl" class="font-bold text-zinc-900 mb-6">Our Mission</flux:heading>
                    <div class="prose prose-lg text-zinc-600 dark:text-zinc-400">
                        <p>
                            The Integrated E-Services Management System aims to provide efficient, transparent, 
                            and accessible government services to all residents of Barangay Aliaga. 
                        </p>
                        <p class="mt-4">
                            Our digital platform ensures that essential services are available 24/7, reducing wait times and 
                            improving service delivery. We are committed to digital transformation that empowers our community.
                        </p>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-900 p-8 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                            <flux:icon.clock class="mx-auto text-blue-600 mb-2" variant="solid" />
                            <div class="font-semibold">24/7 Access</div>
                        </div>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-center">
                            <flux:icon.check-badge class="mx-auto text-emerald-600 mb-2" variant="solid" />
                            <div class="font-semibold">Transparent</div>
                        </div>
                        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-center">
                            <flux:icon.bolt class="mx-auto text-purple-600 mb-2" variant="solid" />
                            <div class="font-semibold">Efficient</div>
                        </div>
                        <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg text-center">
                            <flux:icon.users class="mx-auto text-orange-600 mb-2" variant="solid" />
                            <div class="font-semibold">Accessible</div>
                        </div>
                    </div>
                </div>
            </div>
        </flux:main>
    </div>

    {{-- 4. AVAILABLE SERVICES (From React Code List) --}}
    <div id="services" class="bg-white dark:bg-zinc-900 py-20 border-b border-zinc-200 dark:border-zinc-800">
        <flux:main container>
            <div class="text-center mb-16">
                <flux:heading level="2" size="xl" class="font-bold text-zinc-900">Available Services</flux:heading>
                <flux:subheading>Request documents and stay informed directly through the portal</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Service Items mapped from React Code --}}
                @php
                    $services = [
                        ['title' => 'Barangay Clearance', 'icon' => 'document-check', 'desc' => 'Request official clearance for employment or requirements.'],
                        ['title' => 'Certificate of Indigency', 'icon' => 'heart', 'desc' => 'Certification for medical, financial, or scholarship assistance.'],
                        ['title' => 'Certificate of Residency', 'icon' => 'home', 'desc' => 'Proof of residency for postal ID, bank opening, etc.'],
                        ['title' => 'Business Permit Assist', 'icon' => 'building-storefront', 'desc' => 'Assistance for new business registration and renewals.'],
                        ['title' => 'Community Announcements', 'icon' => 'megaphone', 'desc' => 'Stay updated with the latest news and projects.'],
                        ['title' => 'Event Notifications', 'icon' => 'calendar', 'desc' => 'Get notified about upcoming barangay assemblies and events.'],
                    ];
                @endphp

                @foreach($services as $service)
                <flux:card class="hover:border-blue-500 transition-all duration-300 group">
                    <div class="flex items-start gap-4">
                        <div class="p-3 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                            <flux:icon :icon="$service['icon']" variant="mini" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="mb-1">{{ $service['title'] }}</flux:heading>
                            <p class="text-sm text-zinc-500">{{ $service['desc'] }}</p>
                        </div>
                    </div>
                </flux:card>
                @endforeach
            </div>
        </flux:main>
    </div>

    {{-- 5. HOW TO GET STARTED (From React Code Steps) --}}
    <div id="process" class="bg-zinc-50 dark:bg-zinc-950 py-20">
        <flux:main container>
            <div class="grid md:grid-cols-2 gap-16 items-start">
                <div>
                    <flux:heading level="2" size="xl" class="font-bold text-zinc-900 mb-8">How to Get Started</flux:heading>
                    
                    <div class="relative space-y-0">
                        {{-- Step 1 --}}
                        <div class="flex gap-4 pb-12 relative">
                            <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-zinc-200 dark:bg-zinc-800"></div>
                            <div class="flex-none flex items-center justify-center size-8 rounded-full bg-blue-600 text-white font-bold text-sm z-10">1</div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white">Register Account</h4>
                                <p class="text-sm text-zinc-500 mt-1">Contact the barangay office to register or sign up online to receive credentials.</p>
                            </div>
                        </div>
                        {{-- Step 2 --}}
                        <div class="flex gap-4 pb-12 relative">
                            <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-zinc-200 dark:bg-zinc-800"></div>
                            <div class="flex-none flex items-center justify-center size-8 rounded-full bg-blue-600 text-white font-bold text-sm z-10">2</div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white">Log In</h4>
                                <p class="text-sm text-zinc-500 mt-1">Access the system using your assigned credentials via email.</p>
                            </div>
                        </div>
                        {{-- Step 3 --}}
                        <div class="flex gap-4 pb-12 relative">
                            <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-zinc-200 dark:bg-zinc-800"></div>
                            <div class="flex-none flex items-center justify-center size-8 rounded-full bg-blue-600 text-white font-bold text-sm z-10">3</div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white">Complete Profile</h4>
                                <p class="text-sm text-zinc-500 mt-1">Update your resident information to ensure accurate documentation.</p>
                            </div>
                        </div>
                        {{-- Step 4 --}}
                        <div class="flex gap-4 relative">
                            <div class="flex-none flex items-center justify-center size-8 rounded-full bg-blue-600 text-white font-bold text-sm z-10">4</div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white">Submit Requests</h4>
                                <p class="text-sm text-zinc-500 mt-1">Start submitting requests and accessing services immediately.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Box (From React Code) --}}
                <div class="bg-white dark:bg-zinc-900 p-8 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-lg sticky top-24">
                    <flux:heading level="3" size="lg" class="mb-6 font-bold text-zinc-900">Contact Information</flux:heading>
                    <p class="text-sm text-zinc-600 mb-6">For assistance or questions about the e-services system, please contact:</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-md bg-blue-50 flex items-center justify-center text-blue-600">
                                <flux:icon.envelope variant="mini" />
                            </div>
                            <span class="text-zinc-700 dark:text-zinc-300 text-sm font-medium">info@barangayaliaga.gov.ph</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-md bg-blue-50 flex items-center justify-center text-blue-600">
                                <flux:icon.phone variant="mini" />
                            </div>
                            <span class="text-zinc-700 dark:text-zinc-300 text-sm font-medium">(02) 8123-4567</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-md bg-blue-50 flex items-center justify-center text-blue-600">
                                <flux:icon.map-pin variant="mini" />
                            </div>
                            <span class="text-zinc-700 dark:text-zinc-300 text-sm font-medium">Aliaga Main Road, Philippines</span>
                        </div>
                    </div>

                    <flux:separator class="my-6" />
                    
                    <flux:button href="{{ route('login') }}" variant="filled" class="w-full">
                        Login to Portal
                    </flux:button>
                </div>
            </div>
        </flux:main>
    </div>

    {{-- 6. FOOTER --}}
    <footer class="bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 py-8 text-center text-xs text-zinc-500">
        <flux:main container>
            &copy; {{ date('Y') }} Barangay Aliaga Integrated E-Services. All rights reserved.
        </flux:main>
    </footer>

    @fluxScripts
</body>
</html>