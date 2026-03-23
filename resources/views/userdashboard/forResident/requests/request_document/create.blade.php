<x-layouts::app :title="__('New Request')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('resident.requests.index') }}">Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Document Request</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">New Document Request</flux:heading>
                <flux:subheading>Fill in the details to submit a new document request.</flux:subheading>
            </div>
            
        </div>
                <form action="{{ route('resident.requests.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                    @csrf

                    {{-- 1. Document Type Selection --}}
                    <flux:field>
                        <flux:label>Document Type</flux:label>
                        
                        <flux:select name="document_type_id" placeholder="Select document type..." required>
                            {{-- FIX: Use standard HTML <option> tag here --}}
                            <option value="" disabled selected>Select a document...</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->name }} — ₱{{ number_format($type->fee, 2) }}
                                </option>
                            @endforeach
                        </flux:select>

                        <flux:error name="document_type_id" />
                        
                        <flux:description class="mt-2">
                            Please ensure you have the necessary requirements for the selected document.
                        </flux:description>
                    </flux:field>

                    {{-- 2. Purpose --}}
                    <flux:field x-data="{ purpose: '{{ old('purpose') }}' }">

                        <flux:label>Purpose of Request</flux:label>

                        <flux:error name="purpose" />

                        <flux:textarea 
                            name="purpose"
                            rows="4"
                            maxlength="225"
                            x-model="purpose"
                            placeholder="e.g. For employment requirements, School enrollment..."
                            required>
                            {{ old('purpose') }}
                        </flux:textarea>

                        <div class="flex items-start justify-between mt-1">
                            <flux:description>
                                Briefly explain the reason for your request. This helps us process it accurately.
                            </flux:description>

                            <span class="text-xs text-zinc-500">
                                <span x-text="purpose.length"></span> / 225 characters
                            </span>
                        </div>

                    </flux:field>

                    {{-- 3. File Attachments (Multiple) --}}
                    <flux:field>
                        <flux:label>Attachments (Requirements)</flux:label>
                        <flux:description class="mb-2">
                            Upload valid IDs or supporting documents (Max 5MB per file). Accepted: JPG, PNG, PDF.
                        </flux:description>
                        
                        {{-- Dropzone UI with Alpine.js feedback --}}
                        <div class="flex items-center justify-center w-full" x-data="{ files: null }">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-zinc-300 border-dashed rounded-lg cursor-pointer bg-zinc-50 dark:hover:bg-zinc-800 dark:bg-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:hover:border-zinc-500">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <flux:icon name="cloud-arrow-up" class="w-8 h-8 mb-2 text-zinc-500 dark:text-zinc-400" />
                                    
                                    {{-- Default text --}}
                                    <div x-show="!files || files.length === 0" class="text-center">
                                        <p class="mb-2 text-sm text-zinc-500 dark:text-zinc-400"><span class="font-semibold">Click to upload</span></p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">SVG, PNG, JPG or PDF</p>
                                    </div>

                                    {{-- Success text showing number of files --}}
                                    <div x-show="files && files.length > 0" x-cloak class="text-center">
                                        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400" x-text="files ? files.length + ' file(s) selected' : ''"></p>
                                    </div>
                                </div>
                                <input id="dropzone-file" type="file" name="attachments[]" multiple class="hidden" x-on:change="files = $event.target.files" />
                            </label>
                        </div>
                        
                        <flux:error name="attachments" />
                        <flux:error name="attachments.*" />
                    </flux:field>

                    {{-- 4. Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button href="{{ route('resident.requests.index') }}" variant="ghost">
                            Cancel
                        </flux:button>
                        
                        <flux:button type="submit" variant="primary">
                            Submit Request
                        </flux:button>
                    </div>

                </form>
    </div>

</x-layouts::app>