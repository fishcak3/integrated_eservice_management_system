@props([
    'name',                                  // Unique name for the modal (required)
    'action',                                // The URL to submit the form to (required)
    'title' => 'Are you absolutely sure?',   // Default title
    'buttonText' => 'Yes, Delete',           // Default button text
])

<flux:modal name="{{ $name }}" class="min-w-[22rem]">
    <form action="{{ $action }}" method="POST" class="space-y-6">
        @csrf
        @method('DELETE')

        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            
            <flux:text class="mt-2">
                {{-- We use a slot here so you can pass custom descriptions with bold text --}}
                {{ $slot }}
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="danger">{{ $buttonText }}</flux:button>
        </div>
    </form>
</flux:modal>