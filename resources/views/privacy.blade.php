<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy | Barangay {{ $global_brgy_name ?? 'Portal' }}</title>

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

    {{-- DYNAMIC HEADER (From your layout) --}}
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
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Privacy Policy</h1>
                <p class="text-gray-500 mt-2">Last updated: {{ date('F d, Y') }}</p>
            </div>

            {{-- Policy Text --}}
            <div class="space-y-8 text-gray-700 text-base leading-relaxed">
                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">1. Introduction</h2>
                    <p>
                        Barangay {{ $global_brgy_name ?? 'Aliaga' }} ("we," "our," or "us") is committed to protecting your privacy and ensuring the security of your personal data. This Privacy Policy outlines our practices regarding the collection, use, processing, and protection of your personal information when you access and use the Integrated e-Service Management System (IESMS).
                    </p>
                    <p class="mt-4">
                        This policy is mandated by and complies with <strong>Republic Act No. 10173, also known as the Data Privacy Act of 2012 (DPA)</strong>, its Implementing Rules and Regulations, and other relevant policies issued by the National Privacy Commission (NPC).
                    </p>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">2. Information We Collect</h2>
                    <p>To provide efficient barangay services, we may collect the following types of information when you register or interact with the portal:</p>
                    
                    <h3 class="font-semibold text-gray-800 mt-4 mb-1">A. Standard Personal Information</h3>
                    <ul class="list-disc pl-5 space-y-1 text-gray-600 marker:text-primary">
                        <li>Full name, residential address (including Purok/Sitio), and contact numbers.</li>
                        <li>Email address and account login credentials.</li>
                    </ul>

                    <h3 class="font-semibold text-gray-800 mt-4 mb-1">B. Sensitive Personal Information</h3>
                    <ul class="list-disc pl-5 space-y-1 text-gray-600 marker:text-primary">
                        <li>Date of birth, age, civil status, and gender.</li>
                        <li>Government-issued identification cards and uploaded document copies for identity verification.</li>
                        <li>Sector classifications (e.g., Senior Citizen, PWD, Solo Parent, 4Ps beneficiary) as required for specialized barangay assistance.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">3. Purpose of Data Collection</h2>
                    <p>Your personal and sensitive data is strictly collected and processed for official local government purposes, specifically to:</p>
                    <ul class="list-disc pl-5 space-y-1 mt-2 text-gray-600 marker:text-primary">
                        <li>Process online requests for barangay certificates, clearances, and permits.</li>
                        <li>Verify your identity and legitimate residency within the barangay.</li>
                        <li>Maintain an accurate and updated digital registry of barangay inhabitants (RBI).</li>
                        <li>Facilitate the prompt reporting of local incidents, complaints, or emergencies.</li>
                        <li>Distribute official announcements, warnings, and public service notifications.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">4. Data Protection and Security</h2>
                    <p>
                        We implement strict technical, organizational, and physical security measures to protect your personal data against accidental loss, unauthorized access, fraudulent processing, alteration, or disclosure. Access to the IESMS database is restricted solely to authorized barangay officials and verified system administrators who are bound by strict confidentiality agreements.
                    </p>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">5. Data Sharing and Disclosure</h2>
                    <p>
                        We will never sell, rent, or trade your personal information. We will only share or disclose your data to third parties under the following circumstances:
                    </p>
                    <ul class="list-disc pl-5 space-y-1 mt-2 text-gray-600 marker:text-primary">
                        <li>When required by national government agencies (e.g., DILG, PNP) for law enforcement or official investigations.</li>
                        <li>When necessary to comply with a valid court order or legal obligation.</li>
                        <li>When you grant explicit, written, or digital consent to share your information.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">6. Data Retention</h2>
                    <p>
                        Your personal data will be retained in our system only for as long as you are an active resident of the barangay or as necessary to fulfill the purposes outlined in this policy. Data associated with processed documents (such as clearance logs) will be archived in accordance with standard government records management and retention policies, after which they will be securely deleted or anonymized.
                    </p>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">7. Your Rights as a Data Subject</h2>
                    <p>Under the Data Privacy Act of 2012, you are entitled to the following rights regarding your personal information:</p>
                    <ul class="list-disc pl-5 space-y-1 mt-2 text-gray-600 marker:text-primary">
                        <li><strong>Right to be informed:</strong> You have the right to know how your data is used.</li>
                        <li><strong>Right to access:</strong> You may request a copy of the personal data we hold about you.</li>
                        <li><strong>Right to rectify:</strong> You can correct or update inaccurate or incomplete data via your account dashboard or by visiting the Barangay Hall.</li>
                        <li><strong>Right to erasure or blocking:</strong> You may request the deletion of your account and personal data if it is no longer necessary for the purposes for which it was collected.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-bold text-gray-900 text-xl mb-2">8. Contact Information</h2>
                    <p>
                        If you have any questions, concerns, or requests regarding this Privacy Policy or how we handle your personal data, please reach out to our assigned Data Protection Officer (DPO) or the Barangay Administration:
                    </p>
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200 text-gray-800">
                        <p><strong>Office of the Punong Barangay / Data Protection Officer</strong></p>
                        <p>Barangay {{ $global_brgy_name ?? 'Aliaga' }} Hall</p>
                        <p>Email: {{ $settings['office_email'] ?? 'info@barangayaliaga.gov.ph' }}</p>
                        <p>Contact No: {{ $settings['contact_phone'] ?? 'N/A' }}</p>
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