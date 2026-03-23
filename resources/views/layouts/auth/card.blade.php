<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-100 antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-muted flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-lg flex-col gap-6">
                <a href="{{ route('welcome') }}" class="flex flex-col items-center gap-2 font-medium">
                    @if(isset($global_logo) && $global_logo)
                        <span class="flex h-16 w-16 items-center justify-center rounded-md overflow-hidden">
                            {{-- Assuming images are stored in public/storage. Adjust path if necessary. --}}
                            <img src="{{ asset('storage/' . $global_logo) }}" 
                                alt="{{ $global_brgy_name ?? 'Logo' }}" 
                                class="h-full w-full object-cover">
                        </span>
                    @else
                        {{-- Fallback: Show default icon if no logo is uploaded --}}
                        <span class="flex h-9 w-9 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                        </span>
                    @endif

                    {{-- Use the Global Barangay Name if available --}}
                    <span class="sr-only">{{ $global_brgy_name ?? config('app.name', 'Laravel') }}</span>
                </a>

                <div class="flex flex-col gap-6">
                    <div class="rounded-xl border bg-white dark:bg-stone-950 dark:border-stone-800 text-stone-800 shadow-xs">
                        <div class="px-10 py-8">{{ $slot }}</div>
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
