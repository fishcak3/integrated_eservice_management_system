@props([
    'name',                        // Unique ID for the modal (Required)
    'title' => 'Confirm Action',   // Modal Heading
    'confirmText' => 'Confirm',    // Text on the submit button
    'confirmVariant' => 'primary', // Color of the submit button (e.g., primary, danger)
])

@isset($trigger)
    <flux:modal.trigger :name="$name">
        {{ $trigger }}
    </flux:modal.trigger>
@endisset

{{-- 2. The Modal Content --}}
<flux:modal :name="$name" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            
            {{-- The main message you pass into the component --}}
            <flux:subheading>
                {{ $slot }}
            </flux:subheading>
        </div>

        <div class="flex justify-end gap-2">
            {{-- Cancel Button --}}
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>

            {{-- Submit Button (Submits whatever <form> this component sits inside) --}}
            <flux:button type="submit" :variant="$confirmVariant">
                {{ $confirmText }}
            </flux:button>
        </div>
    </div>
</flux:modal>