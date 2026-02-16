<x-layouts::app :title="__('New Request')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">

        {{-- Header Section --}}
        <div>
            <flux:heading size="lg">New Document Request</flux:heading>
            <flux:subheading>Submit a new request for barangay documents.</flux:subheading>
        </div>

        {{-- Form Container --}}
        <div class="w-full max-w-2xl">
            <flux:card>
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
                    <flux:field>
                        <flux:input 
                            name="purpose" 
                            label="Purpose of Request" 
                            placeholder="e.g. For Employment, Scholarship Application, Bank Account Opening" 
                            value="{{ old('purpose') }}"
                            required 
                        />
                        <flux:error name="purpose" />
                    </flux:field>

                    {{-- 3. File Attachments (Multiple) --}}
                    <flux:field>
                        <flux:label>Attachments (Requirements)</flux:label>
                        <flux:description class="mb-2">
                            Upload valid IDs or supporting documents (Max 5MB per file). Accepted: JPG, PNG, PDF.
                        </flux:description>
                        
                        {{-- Dropzone UI --}}
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-zinc-300 border-dashed rounded-lg cursor-pointer bg-zinc-50 dark:hover:bg-zinc-800 dark:bg-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:hover:border-zinc-500">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <flux:icon name="cloud-arrow-up" class="w-8 h-8 mb-2 text-zinc-500 dark:text-zinc-400" />
                                    <p class="mb-2 text-sm text-zinc-500 dark:text-zinc-400"><span class="font-semibold">Click to upload</span></p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">SVG, PNG, JPG or PDF</p>
                                </div>
                                <input id="dropzone-file" type="file" name="attachments[]" multiple class="hidden" />
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
            </flux:card>
        </div>
    </div>

</x-layouts::app>