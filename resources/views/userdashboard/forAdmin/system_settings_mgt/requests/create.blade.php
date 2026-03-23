<x-layouts::app>

    <x-slot:header>
        @if(request('type') === 'complaint')
            <flux:breadcrumbs class="mb-2">
                <flux:breadcrumbs.item>Settings</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('settings.request', ['type' => 'complaint']) }}" wire:navigate>Complaints</flux:breadcrumbs.item>
                <flux:breadcrumbs.item :current="true">Add Complaint Type</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        @else
            <flux:breadcrumbs class="mb-2">
                <flux:breadcrumbs.item>Settings</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('settings.request', ['type' => 'document']) }}" wire:navigate>Document Requests</flux:breadcrumbs.item>
                <flux:breadcrumbs.item :current="true">Add Document Type</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        @endif
    </x-slot:header>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Page Header --}}
        @if(request('type', 'document') === 'document')
            <div>
                <flux:heading size="lg" level="1">Add Document Type</flux:heading>
                <flux:subheading>Create a new document type for residents to request.</flux:subheading>
            </div>
        @else
            <div>
                <flux:heading size="lg" level="1">Add Complaint Type</flux:heading>
                <flux:subheading>Define a new category and severity level for resident complaints.</flux:subheading>
            </div>
        @endif

        {{-- Form Card --}}
        <flux:card class="max-w-2xl">
            <form method="POST" action="{{ route('settings.request.store', ['type' => request('type', 'document')]) }}" class="flex flex-col gap-6">
                @csrf

                @if(request('type', 'document') === 'document')
                    {{-- DOCUMENT TYPE FORM --}}
                    
                    <flux:input 
                        name="name" 
                        label="Document Name" 
                        placeholder="e.g., Barangay Clearance" 
                        value="{{ old('name') }}" 
                        required 
                    />

                    <flux:input 
                        type="number" 
                        step="0.01" 
                        name="fee" 
                        label="Processing Fee (₱)" 
                        placeholder="0.00" 
                        value="{{ old('fee', '0.00') }}" 
                        required 
                    />

                    <flux:textarea 
                        name="requirements" 
                        label="Requirements" 
                        placeholder="List required IDs or documents needed (optional)..." 
                        rows="4"
                    >{{ old('requirements') }}</flux:textarea>

                    <flux:checkbox 
                        name="is_active" 
                        label="Active Status" 
                        description="Allow residents to request this document immediately."
                        checked="{{ old('is_active', true) }}" 
                        value="1"
                    />

                @else
                    {{-- COMPLAINT TYPE FORM --}}
                    
                    <flux:input 
                        name="name" 
                        label="Complaint Name" 
                        placeholder="e.g., Noise Complaint, Property Dispute" 
                        value="{{ old('name') }}" 
                        required 
                    />

                    <flux:select name="severity_level" label="Default Severity Level" required>
                        <flux:select.option value="low" :selected="old('severity_level') === 'low'">Low</flux:select.option>
                        <flux:select.option value="medium" :selected="old('severity_level', 'medium') === 'medium'">Medium</flux:select.option>
                        <flux:select.option value="high" :selected="old('severity_level') === 'high'">High</flux:select.option>
                        <flux:select.option value="critical" :selected="old('severity_level') === 'critical'">Critical</flux:select.option>
                    </flux:select>

                    <flux:textarea 
                        name="description" 
                        label="Description" 
                        placeholder="Provide guidelines or descriptions for this complaint type (optional)..." 
                        rows="4"
                    >{{ old('description') }}</flux:textarea>

                @endif

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 mt-4">
                    <flux:button type="submit" variant="primary">Save Type</flux:button>
                    <flux:button href="{{ route('settings.request', ['type' => request('type', 'document')]) }}" wire:navigate variant="ghost">Cancel</flux:button>
                </div>

            </form>
        </flux:card>

    </div>
</x-layouts::app>