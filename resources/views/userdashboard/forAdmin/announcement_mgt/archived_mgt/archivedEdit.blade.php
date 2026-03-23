<x-layouts::app title="Review & Republish Announcement">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('admin.announcements.archived') }}">Archived Announcements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Review & Edit</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    <div class="flex flex-col gap-6">
        <flux:card>
            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                <div>
                    <flux:heading size="lg">Review & Republish Announcement</flux:heading>
                    <flux:subheading>Update the details, adjust the dates, and change the status to "Published" to make this announcement live again.</flux:subheading>
                </div>
            </div>

            {{-- Standard Form Submission --}}
            <form action="{{ route('admin.announcements.update-status', $announcement) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')
                
                {{-- Hidden input to automatically set status to published --}}
                <input type="hidden" name="status" value="published">
                
                {{-- Title --}}
                <flux:input name="title" label="Title" value="{{ old('title', $announcement->title) }}" placeholder="e.g. Scheduled Water Interruption" required />

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    {{-- Publish Date --}}
                    <flux:input type="datetime-local" name="publish_at" label="Publish Date" value="{{ old('publish_at', $announcement->publish_at?->format('Y-m-d\TH:i')) }}" />

                    {{-- Expiration Date --}}
                    <flux:input type="datetime-local" name="expires_at" label="Expiration Date (Optional)" value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}" description="Auto-archives after this date." />
                </div>

                {{-- Image Upload Section --}}
                <div>
                    <flux:label>Cover Image (Optional)</flux:label>
                    
                    <div class="mt-2 space-y-4">
                        {{-- File Input --}}
                        <flux:input type="file" name="cover_image" accept="image/*" />
                        
                        {{-- Existing Image Preview --}}
                        @if ($announcement->cover_image)
                            <div class="relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 w-full sm:w-64 mt-2">
                                <img src="{{ Storage::url($announcement->cover_image) }}" class="w-full h-auto object-cover" alt="Current Image">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Content --}}
                <flux:textarea name="content" label="Content" rows="6" placeholder="Write the details here..." required>{{ old('content', $announcement->content) }}</flux:textarea>

                {{-- Actions --}}
                <div class="flex justify-end gap-2 mt-2">
                    <flux:button href="{{ route('admin.announcements.archived') }}" variant="ghost">Cancel</flux:button>
                    
                    {{-- The Dynamic Modal --}}
                    <x-confirm-modal 
                        name="confirm-republish" 
                        title="Confirm Republish" 
                        confirmText="Yes, Republish" 
                        confirmVariant="primary"
                    >
                        <x-slot name="trigger">
                            <flux:button variant="primary">Republish</flux:button>
                        </x-slot>

                        Are you sure you want to republish "<strong>{{ $announcement->title }}</strong>"? Please ensure your updated dates and content are correct before making it live.
                    </x-confirm-modal>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>