<footer class="mt-auto w-full border-t border-green-700 bg-green-800 py-6">

    <div class="px-6 flex flex-col items-center gap-2 text-sm text-green-100 text-center">

        <div class="font-semibold text-md">
            Barangay {{ $global_brgy_name ?? ' Portal' }} Integrated E-Services Management System
        </div>
        
        <div>
            &copy; {{ date('Y') }} Barangay {{ $global_brgy_name ?? ' Portal' }} | All Rights Reserved
        </div>
        
        <div class="flex gap-4 mt-2">
            <a href="#" class="hover:text-white transition">Privacy Policy</a>
            <a href="#" class="hover:text-white transition">Terms of Service</a>
            <a href="#" class="hover:text-white transition">Support</a>
        </div>
        
    </div>
</footer>