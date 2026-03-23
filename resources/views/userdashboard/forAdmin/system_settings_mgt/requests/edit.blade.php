<x-layouts::app>

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item>Settings</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('settings.request', ['type' => $type]) }}">{{ ucfirst($type) }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $item->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
      
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">Edit {{ ucfirst($type) }} Type</flux:heading>
                <flux:subheading>Update the details and configuration for this {{ $type }}.</flux:subheading>
            </div>
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('settings.request', ['type' => $type]) }}" wire:navigate>
                Back to {{ ucfirst($type) }} Settings
            </flux:button>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-red-50 text-red-600 p-4 rounded-lg text-sm border border-red-200 mb-6">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <flux:card>
            <form action="{{ route('settings.request.update', ['id' => $item->id, 'type' => $type]) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @if($type === 'document')
                    {{-- DOCUMENT EDIT FORM --}}
                    
                    <flux:input 
                        name="name" 
                        label="Document Name" 
                        placeholder="e.g., Barangay Clearance" 
                        value="{{ old('name', $item->name) }}" 
                        required 
                    />

                    <flux:input 
                        type="number" 
                        step="0.01" 
                        name="fee" 
                        label="Processing Fee (₱)" 
                        placeholder="50.00" 
                        value="{{ old('fee', $item->fee) }}" 
                        required 
                    />

                    <flux:textarea 
                        name="requirements" 
                        label="Requirements" 
                        placeholder="e.g., 1 Valid ID, Cedula..." 
                        rows="4"
                    >{{ old('requirements', $item->requirements) }}</flux:textarea>

                    <div class="pt-2">
                        <flux:switch 
                            name="is_active" 
                            label="Active Status" 
                            description="If disabled, residents will not be able to request this document." 
                            value="1" 
                            :checked="old('is_active', $item->is_active)" 
                        />
                    </div>

                @else
                    {{-- COMPLAINT EDIT FORM --}}
                    
                    <flux:input 
                        name="name" 
                        label="Complaint Category Name" 
                        placeholder="e.g., Noise Complaint" 
                        value="{{ old('name', $item->name) }}" 
                        required 
                    />

                    <flux:select name="severity_level" label="Default Severity Level" required>
                        <flux:select.option value="low" :selected="old('severity_level', $item->severity_level) === 'low'">Low</flux:select.option>
                        <flux:select.option value="medium" :selected="old('severity_level', $item->severity_level) === 'medium'">Medium</flux:select.option>
                        <flux:select.option value="high" :selected="old('severity_level', $item->severity_level) === 'high'">High</flux:select.option>
                        <flux:select.option value="critical" :selected="old('severity_level', $item->severity_level) === 'critical'">Critical</flux:select.option>
                    </flux:select>

                    <flux:textarea 
                        name="description" 
                        label="Description" 
                        placeholder="Briefly describe what this complaint entails..." 
                        rows="4"
                    >{{ old('description', $item->description) }}</flux:textarea>

                @endif

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button variant="ghost" href="{{ route('settings.request', ['type' => $type]) }}" wire:navigate>Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save Changes</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>