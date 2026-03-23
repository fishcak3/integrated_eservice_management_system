@props([
    'title' => 'Are you sure?',
    'description' => 'This action cannot be undone.',
])

<flux:card class="space-y-6 border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-900/20">
    <div class="flex">
        <div class="flex-1">
            <flux:heading size="lg" class="text-red-800 dark:text-red-400">{{ $title }}</flux:heading>

            <flux:text class="mt-2 text-red-600 dark:text-red-300">
                {!! $description !!}
            </flux:text>
        </div>
    </div>

    <div class="flex gap-4 items-center">
        <flux:spacer />
        {{-- We use a slot here so you can pass custom forms or buttons! --}}
        {{ $slot }}
    </div>
</flux:card>