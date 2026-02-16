<div class="flex flex-col gap-6">
    <flux:card>
        <form wire:submit="save" class="space-y-6">
            
            {{-- Title --}}
            <flux:input label="Title" wire:model="title" placeholder="e.g. Scheduled Water Interruption" required />

            {{-- Status & Priority --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <flux:select label="Status" wire:model="status">
                    <flux:select.option value="draft">Draft</flux:select.option>
                    <flux:select.option value="published">Published</flux:select.option>
                    <flux:select.option value="archived">Archived</flux:select.option>
                </flux:select>

                <flux:select label="Priority" wire:model="priority">
                    <flux:select.option value="normal">Normal</flux:select.option>
                    <flux:select.option value="high">High Importance</flux:select.option>
                    <flux:select.option value="emergency">Emergency</flux:select.option>
                </flux:select>
            </div>

            {{-- Pinned --}}
            <flux:checkbox label="Pin to top" wire:model="is_pinned" description="Keep this announcement at the top of the list." />

            {{-- 
                FIXED IMAGE UPLOAD SECTION 
                Replaced broken 'dropzone' with standard 'flux:input type="file"'
            --}}
            <div>
                <flux:label>Cover Image (Optional)</flux:label>
                
                <div class="mt-2 space-y-4">
                    {{-- 1. The File Input --}}
                    <flux:input type="file" wire:model="cover_image" accept="image/*" />
                    <flux:error name="cover_image" />

                    {{-- 2. New Upload Preview --}}
                    @if ($cover_image)
                        <div class="relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 w-full sm:w-64">
                            <img src="{{ $cover_image->temporaryUrl() }}" class="w-full h-auto object-cover" alt="Preview">
                            <button type="button" wire:click="removeImage" class="absolute top-2 right-2 bg-black/50 hover:bg-black/70 text-white rounded-full p-1">
                                <flux:icon name="x-mark" size="xs" />
                            </button>
                            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-2 truncate">
                                {{ $cover_image->getClientOriginalName() }}
                            </div>
                        </div>
                    @endif

                    {{-- 3. Existing Image Preview (Edit Mode) --}}
                    @if (! $cover_image && $existing_image)
                        <div class="relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 w-full sm:w-64">
                            <img src="{{ Storage::url($existing_image) }}" class="w-full h-auto object-cover" alt="Current Image">
                            <button type="button" wire:click="removeExistingImage" class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1" title="Remove current image">
                                <flux:icon name="trash" size="xs" />
                            </button>
                            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-2">
                                Current Image
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Content --}}
            <flux:textarea label="Content" wire:model="content" rows="6" placeholder="Write the details here..." required />

            {{-- Actions --}}
            <div class="flex justify-end gap-2">
                <flux:button href="{{ route('announcements.index') }}" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">
                    <span wire:loading.remove>Save Announcement</span>
                    <span wire:loading>Saving...</span>
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>