<x-layouts::auth.card :title="$title ?? null">
    {{ $slot }}

    {{-- TERMS OF SERVICE MODAL --}}
    <flux:modal name="terms-modal" class="md:w-[800px] space-y-6">
        <div>
            <flux:heading size="lg">Terms of Service</flux:heading>
            <flux:subheading>Last updated: {{ date('F d, Y') }}</flux:subheading>
        </div>

        {{-- Scrollable content box --}}
        <div class="h-[60vh] overflow-y-auto pr-4 space-y-6 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
            
            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">AGREEMENT TO OUR TERMS</p>
                <div class="space-y-3">
                    <p>Welcome to the Integrated e-Service Management System (IESMS) of Barangay {{ $global_brgy_name ?? 'Aliaga' }} ("Barangay," "we," "us," or "our").</p>
                    <p>These Terms of Service constitute a legally binding agreement made between you, whether personally or on behalf of a household or entity ("you"), and the Barangay, concerning your access to and use of our IESMS web portal.</p>
                    <p>By registering, accessing, or using the system, you acknowledge that you have read, understood, and agreed to be bound by all of these Terms of Service. <strong class="text-zinc-900 dark:text-white">IF YOU DO NOT AGREE WITH ALL OF THESE TERMS, YOU ARE EXPRESSLY PROHIBITED FROM USING THE SYSTEM AND MUST DISCONTINUE USE IMMEDIATELY.</strong></p>
                </div>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">1. PURPOSE OF THE SYSTEM</p>
                <p>The IESMS is an official government portal designed to facilitate the delivery of public services to verified residents. This includes, but is not limited to, the online request of barangay certificates, clearances, reporting of local incidents, and the dissemination of official announcements.</p>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">2. USER REGISTRATION AND VERIFICATION</p>
                <p>To access certain features of the system, you are required to register and create an account. By registering, you represent and warrant that:</p>
                <ul class="list-disc pl-5 mt-2 space-y-1">
                    <li>All registration information you submit (including names, addresses, and uploaded IDs) is true, accurate, current, and complete.</li>
                    <li>You are a legitimate resident or have official business with Barangay {{ $global_brgy_name ?? 'Aliaga' }}.</li>
                    <li>You will maintain the accuracy of your information and promptly update it as necessary.</li>
                    <li>You will keep your account password confidential and be responsible for all activities that occur under your account.</li>
                </ul>
                <p class="mt-3 font-semibold text-red-600 dark:text-red-400">Note: Submitting falsified documents, fake identification, or committing identity theft in a government portal is a criminal offense punishable under Philippine law (e.g., Falsification of Public Documents).</p>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">3. PROHIBITED ACTIVITIES</p>
                <p>You may not access or use the system for any purpose other than its official intended use. As a user, you agree not to:</p>
                <ul class="list-disc pl-5 mt-2 space-y-1">
                    <li>Impersonate another resident, barangay official, or staff member.</li>
                    <li>Submit false requests, frivolous complaints, or fabricated incident reports.</li>
                    <li>Use the system for any unauthorized commercial purposes or advertising.</li>
                    <li>Attempt to bypass, disable, or interfere with security-related features of the portal.</li>
                    <li>Upload viruses, trojans, or any malicious code that will affect the functionality or operation of the system.</li>
                    <li>Harass, intimidate, or threaten barangay officials or other users through the platform.</li>
                </ul>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">4. PRIVACY AND DATA PROTECTION</p>
                <p>Your use of the IESMS is also governed by our Privacy Policy. By using the system, you consent to the collection, processing, and storage of your personal data in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173), strictly for official barangay documentation and service delivery purposes.</p>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">5. SYSTEM MODIFICATIONS AND AVAILABILITY</p>
                <p>We reserve the right to change, modify, or remove the contents of the system at any time or for any reason at our sole discretion without notice. We cannot guarantee the system will be available at all times. We may experience hardware, software, or other problems resulting in interruptions, delays, or errors.</p>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">6. CONTACT US</p>
                <p>To resolve a complaint regarding the system or to receive further information regarding its use, please visit the Barangay Hall during standard office hours or contact the official administration desk.</p>
            </div>

        </div>

        <div class="flex justify-end mb-0 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:modal.close>
                <flux:button variant="primary">I Understand</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

    {{-- PRIVACY POLICY MODAL --}}
    <flux:modal name="privacy-modal" class="md:w-[800px] space-y-6">
        <div>
            <flux:heading size="lg">Privacy Policy</flux:heading>
            <flux:subheading>Last updated: {{ date('F d, Y') }}</flux:subheading>
        </div>

        {{-- Scrollable content box --}}
        <div class="h-[60vh] overflow-y-auto pr-4 space-y-6 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
            
            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">1. Introduction</p>
                <p>Barangay {{ $global_brgy_name ?? 'Aliaga' }} ("we," "our," or "us") is committed to protecting your privacy and ensuring the security of your personal data. This Privacy Policy outlines our practices regarding the collection, use, processing, and protection of your personal information when you access and use the Integrated e-Service Management System (IESMS).</p>
                <p class="mt-2">This policy is mandated by and complies with <strong class="text-zinc-900 dark:text-white">Republic Act No. 10173, also known as the Data Privacy Act of 2012 (DPA)</strong>, its Implementing Rules and Regulations, and other relevant policies issued by the National Privacy Commission (NPC).</p>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">2. Information We Collect</p>
                <p>To provide efficient barangay services, we may collect the following types of information when you register or interact with the portal:</p>
                
                <p class="font-semibold text-zinc-800 dark:text-zinc-200 mt-3">A. Standard Personal Information</p>
                <ul class="list-disc pl-5 mt-1 space-y-1">
                    <li>Full name, residential address (including Purok/Sitio), and contact numbers.</li>
                    <li>Email address and account login credentials.</li>
                </ul>

                <p class="font-semibold text-zinc-800 dark:text-zinc-200 mt-3">B. Sensitive Personal Information</p>
                <ul class="list-disc pl-5 mt-1 space-y-1">
                    <li>Date of birth, age, civil status, and gender.</li>
                    <li>Government-issued identification cards and uploaded document copies for identity verification.</li>
                    <li>Sector classifications (e.g., Senior Citizen, PWD, Solo Parent, 4Ps beneficiary) as required for specialized barangay assistance.</li>
                </ul>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">3. Purpose of Data Collection</p>
                <p>Your personal and sensitive data is strictly collected and processed for official local government purposes, specifically to:</p>
                <ul class="list-disc pl-5 mt-2 space-y-1">
                    <li>Process online requests for barangay certificates, clearances, and permits.</li>
                    <li>Verify your identity and legitimate residency within the barangay.</li>
                    <li>Maintain an accurate and updated digital registry of barangay inhabitants (RBI).</li>
                    <li>Facilitate the prompt reporting of local incidents, complaints, or emergencies.</li>
                    <li>Distribute official announcements, warnings, and public service notifications.</li>
                </ul>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">4. Data Protection and Security</p>
                <p>We implement strict technical, organizational, and physical security measures to protect your personal data against accidental loss, unauthorized access, fraudulent processing, alteration, or disclosure. Access to the IESMS database is restricted solely to authorized barangay officials and verified system administrators who are bound by strict confidentiality agreements.</p>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">5. Data Sharing and Disclosure</p>
                <p>We will never sell, rent, or trade your personal information. We will only share or disclose your data to third parties under the following circumstances:</p>
                <ul class="list-disc pl-5 mt-2 space-y-1">
                    <li>When required by national government agencies (e.g., DILG, PNP) for law enforcement or official investigations.</li>
                    <li>When necessary to comply with a valid court order or legal obligation.</li>
                    <li>When you grant explicit, written, or digital consent to share your information.</li>
                </ul>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">6. Data Retention</p>
                <p>Your personal data will be retained in our system only for as long as you are an active resident of the barangay or as necessary to fulfill the purposes outlined in this policy. Data associated with processed documents (such as clearance logs) will be archived in accordance with standard government records management and retention policies, after which they will be securely deleted or anonymized.</p>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">7. Your Rights as a Data Subject</p>
                <p>Under the Data Privacy Act of 2012, you are entitled to the following rights regarding your personal information:</p>
                <ul class="list-disc pl-5 mt-2 space-y-1">
                    <li><strong class="text-zinc-800 dark:text-zinc-200">Right to be informed:</strong> You have the right to know how your data is used.</li>
                    <li><strong class="text-zinc-800 dark:text-zinc-200">Right to access:</strong> You may request a copy of the personal data we hold about you.</li>
                    <li><strong class="text-zinc-800 dark:text-zinc-200">Right to rectify:</strong> You can correct or update inaccurate or incomplete data via your account dashboard or by visiting the Barangay Hall.</li>
                    <li><strong class="text-zinc-800 dark:text-zinc-200">Right to erasure or blocking:</strong> You may request the deletion of your account and personal data if it is no longer necessary for the purposes for which it was collected.</li>
                </ul>
            </div>

            <div>
                <p class="font-bold text-zinc-900 dark:text-white mb-2 text-base">8. Contact Information</p>
                <p>If you have any questions, concerns, or requests regarding this Privacy Policy or how we handle your personal data, please reach out to our assigned Data Protection Officer (DPO) or the Barangay Administration:</p>
                
                <div class="mt-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-300">
                    <p class="font-semibold text-zinc-900 dark:text-white mb-1">Office of the Punong Barangay / Data Protection Officer</p>
                    <p>Barangay {{ $global_brgy_name ?? 'Aliaga' }} Hall</p>
                    <p class="mt-2">Email: {{ $settings['office_email'] ?? 'info@barangayaliaga.gov.ph' }}</p>
                    <p>Contact No: {{ $settings['contact_phone'] ?? 'N/A' }}</p>
                </div>
            </div>

        </div>

        <div class="flex justify-end mb-0 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:modal.close>
                <flux:button variant="primary">I Understand</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</x-layouts::auth.card>