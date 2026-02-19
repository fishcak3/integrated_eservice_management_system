<div class="flex flex-col gap-6">
    <flux:card>
        <form wire:submit="save" class="space-y-6">
            
            {{-- Title --}}
            <flux:input label="Title" wire:model="title" placeholder="e.g. Scheduled Water Interruption" required />

            {{-- Status & Dates Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                {{-- Status (Enum: published, archived) --}}
                <flux:select label="Status" wire:model="status">
                    <flux:select.option value="published">Published</flux:select.option>
                    <flux:select.option value="archived">Archived</flux:select.option>
                </flux:select>

                {{-- Publish Date --}}
                <flux:input type="datetime-local" label="Publish Date" wire:model="publish_at" />

                {{-- Expiration Date --}}
                <flux:input type="datetime-local" label="Expiration Date (Optional)" wire:model="expires_at" description="Auto-archives after this date." />
            </div>

            {{-- Image Upload Section --}}
            <div>
                <flux:label>Cover Image (Optional)</flux:label>
                
                <div class="mt-2 space-y-4">
                    {{-- 1. File Input --}}
                    <flux:input type="file" wire:model="cover_image" accept="image/*" />
                    <flux:error name="cover_image" />

                    {{-- 2. New Upload Preview --}}
                    @if ($cover_image)
                        <div class="relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 w-full sm:w-64">
                            <img src="{{ $cover_image->temporaryUrl() }}" class="w-full h-auto object-cover" alt="Preview">
                            <button type="button" wire:click="$set('cover_image', null)" class="absolute top-2 right-2 bg-black/50 hover:bg-black/70 text-white rounded-full p-1">
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
                            {{-- Optional: Add logic in component to delete existing image --}}
                            {{-- <button type="button" wire:click="removeExistingImage" ... > --}}
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