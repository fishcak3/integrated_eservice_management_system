<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service | Barangay {{ $global_brgy_name ?? 'Portal' }}</title>

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
</head>
<body class="antialiased min-h-screen bg-white text-slate-800 font-sans flex flex-col">

    {{-- DYNAMIC HEADER --}}
    <header class="bg-primary shadow-sm border-b sticky top-0 z-50 shrink-0">
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

    {{-- MAIN CONTENT (Clean, White, Document Style) --}}
    <main class="flex-grow w-full py-12 sm:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Title Header --}}
            <div class="mb-10 border-b border-gray-200 pb-6">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Terms of Service</h1>
                <p class="text-gray-500 mt-2">Last updated: {{ date('F d, Y') }}</p>
            </div>

            {{-- Policy Text --}}
            <div class="space-y-8 text-gray-700 text-base leading-relaxed">
                
                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">AGREEMENT TO OUR TERMS</h2>
                    <div class="mt-2 space-y-4">
                        <p>Welcome to the Integrated e-Service Management System (IESMS) of Barangay {{ $global_brgy_name ?? 'Aliaga' }} ("Barangay," "we," "us," or "our").</p>
                        <p>These Terms of Service constitute a legally binding agreement made between you, whether personally or on behalf of a household or entity ("you"), and the Barangay, concerning your access to and use of our IESMS web portal.</p>
                        <p>By registering, accessing, or using the system, you acknowledge that you have read, understood, and agreed to be bound by all of these Terms of Service. <strong>IF YOU DO NOT AGREE WITH ALL OF THESE TERMS, YOU ARE EXPRESSLY PROHIBITED FROM USING THE SYSTEM AND MUST DISCONTINUE USE IMMEDIATELY.</strong></p>
                    </div>
                </div>
                
                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">1. PURPOSE OF THE SYSTEM</h2>
                    <p class="mt-2">The IESMS is an official government portal designed to facilitate the delivery of public services to verified residents. This includes, but is not limited to, the online request of barangay certificates, clearances, reporting of local incidents, and the dissemination of official announcements.</p>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">2. USER REGISTRATION AND VERIFICATION</h2>
                    <p class="mt-2">To access certain features of the system, you are required to register and create an account. By registering, you represent and warrant that:</p>
                    <ul class="list-disc pl-5 space-y-2 mt-4 text-gray-600 marker:text-primary">
                        <li>All registration information you submit (including names, addresses, and uploaded IDs) is true, accurate, current, and complete.</li>
                        <li>You are a legitimate resident or have official business with Barangay {{ $global_brgy_name ?? 'Aliaga' }}.</li>
                        <li>You will maintain the accuracy of your information and promptly update it as necessary.</li>
                        <li>You will keep your account password confidential and be responsible for all activities that occur under your account.</li>
                    </ul>
                    <p class="mt-4 font-semibold text-red-600">Note: Submitting falsified documents, fake identification, or committing identity theft in a government portal is a criminal offense punishable under Philippine law (e.g., Falsification of Public Documents).</p>
                </div>
                
                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">3. PROHIBITED ACTIVITIES</h2>
                    <p class="mt-2">You may not access or use the system for any purpose other than its official intended use. As a user, you agree not to:</p>
                    <ul class="list-disc pl-5 space-y-2 mt-4 text-gray-600 marker:text-primary">
                        <li>Impersonate another resident, barangay official, or staff member.</li>
                        <li>Submit false requests, frivolous complaints, or fabricated incident reports.</li>
                        <li>Use the system for any unauthorized commercial purposes or advertising.</li>
                        <li>Attempt to bypass, disable, or interfere with security-related features of the portal.</li>
                        <li>Upload viruses, trojans, or any malicious code that will affect the functionality or operation of the system.</li>
                        <li>Harass, intimidate, or threaten barangay officials or other users through the platform.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">4. PRIVACY AND DATA PROTECTION</h2>
                    <p class="mt-2">Your use of the IESMS is also governed by our Privacy Policy. By using the system, you consent to the collection, processing, and storage of your personal data in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173), strictly for official barangay documentation and service delivery purposes.</p>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">5. SYSTEM MODIFICATIONS AND AVAILABILITY</h2>
                    <p class="mt-2">We reserve the right to change, modify, or remove the contents of the system at any time or for any reason at our sole discretion without notice. We cannot guarantee the system will be available at all times. We may experience hardware, software, or other problems resulting in interruptions, delays, or errors.</p>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">6. CONTACT US</h2>
                    <div class="mt-2 space-y-2">
                        <p>To resolve a complaint regarding the system or to receive further information regarding its use, please visit the Barangay Hall during standard office hours or contact the official administration desk.</p>
                    </div>
                </div>

            </div>

        </div>
    </main>

    {{-- DYNAMIC FOOTER --}}
    @include('partials.footer')

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>

    {{-- CHATBOT INCLUSION --}}
    @include('partials.chatbot')

</body>
</html>