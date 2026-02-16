@php
    $brgyName = \App\Models\BrgySetting::get('barangay_name') ?? 'Aliaga';
@endphp

<footer class="mt-auto w-full border-t border-zinc-200 bg-white py-6 dark:border-zinc-800 dark:bg-zinc-900">

    <div class="px-6 flex flex-col items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 text-center">

        <div class="font-semibold text-zinc-900 dark:text-white">
            Barangay {{ $brgyName }} Integrated E-Services Management System
        </div>
        
        <div>
            &copy; {{ date('Y') }} Barangay {{ $brgyName }} | All Rights Reserved
        </div>
        
        <div class="flex gap-4 mt-2">
            <a href="#" class="hover:text-zinc-900 dark:hover:text-white transition">Privacy Policy</a>
            <a href="#" class="hover:text-zinc-900 dark:hover:text-white transition">Terms of Service</a>
            <a href="#" class="hover:text-zinc-900 dark:hover:text-white transition">Support</a>
        </div>
        
    </div>
</footer>