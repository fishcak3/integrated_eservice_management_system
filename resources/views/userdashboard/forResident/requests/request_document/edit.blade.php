<x-layouts::app :title="__('Edit Request')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">

        {{-- Header Section --}}
        <div>
            <flux:heading size="lg">Edit Request</flux:heading>
            <flux:subheading>
                Update details for request <span class="font-mono font-bold">{{ $documentRequest->tracking_code }}</span>
            </flux:subheading>
        </div>

        {{-- Form Container --}}
        <div class="w-full max-w-2xl">
            <flux:card>
                <form action="{{ route('resident.requests.update', $documentRequest->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                    @csrf
                    @method('PUT')

                    {{-- 1. Document Type Selection --}}
                    <flux:field>
                        <flux:label>Document Type</flux:label>
                        
                        <flux:select name="document_type_id" placeholder="Select document type..." required>
                            <option value="" disabled>Select a document...</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}" {{ $documentRequest->document_type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} — ₱{{ number_format($type->fee, 2) }}
                                </option>
                            @endforeach
                        </flux:select>

                        <flux:error name="document_type_id" />
                    </flux:field>

                    {{-- 2. Purpose --}}
                    <flux:field>
                        <flux:input 
                            name="purpose" 
                            label="Purpose of Request" 
                            value="{{ old('purpose', $documentRequest->purpose) }}"
                            required 
                        />
                        <flux:error name="purpose" />
                    </flux:field>

                    {{-- 3. Manage Existing Attachments --}}
                    @if($documentRequest->attachments->count() > 0)
                        <flux:field>
                            <flux:label>Current Attachments</flux:label>
                            <div class="space-y-2 mt-2">
                                @foreach($documentRequest->attachments as $file)
                                    <div class="flex items-center justify-between p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <flux:icon name="paper-clip" class="text-zinc-500 shrink-0" />
                                            <span class="text-sm truncate">{{ $file->file_name ?? basename($file->file_path) }}</span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-blue-500 hover:underline text-xs">View</a>
                                            
                                            {{-- Individual File Delete Checkbox --}}
                                            <label class="flex items-center gap-2 text-xs text-red-500 cursor-pointer hover:bg-red-50 p-1 rounded">
                                                <input type="checkbox" name="delete_attachments[]" value="{{ $file->id }}" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                Remove
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <flux:description>Check "Remove" to delete specific files upon saving.</flux:description>
                        </flux:field>
                    @endif

                    {{-- 4. Add New Attachments --}}
                    <flux:field>
                        <flux:label>Add New Attachments</flux:label>
                        <flux:description class="mb-2">
                            Upload additional files if needed.
                        </flux:description>
                        
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
                    </flux:field>

                    {{-- 5. Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button href="{{ route('resident.requests.index', $documentRequest->id) }}" variant="ghost">
                            Cancel
                        </flux:button>
                        
                        <flux:button type="submit" variant="primary">
                            Save Changes
                        </flux:button>
                    </div>

                </form>
            </flux:card>
        </div>
    </div>

</x-layouts::app>