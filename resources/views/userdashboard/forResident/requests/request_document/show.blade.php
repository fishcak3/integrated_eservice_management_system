<x-layouts::app :title="__('Request Details')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">

        {{-- Header Section --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">Request Details</flux:heading>
                <flux:subheading>View the status and details of your document request.</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:button href="{{ route('resident.requests.index') }}" icon="arrow-left" variant="ghost">
                    Back to List
                </flux:button>
                
                {{-- Only show Edit/Cancel if pending --}}
                @if($documentRequest->status === 'pending')
                    <flux:button href="{{ route('resident.requests.edit', $documentRequest->id) }}" icon="pencil-square">
                        Edit
                    </flux:button>
                    
                    <flux:modal.trigger name="cancel-request-modal">
                        <flux:button variant="danger" icon="trash">Cancel Request</flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                <flux:card>
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <flux:label>Tracking Code</flux:label>
                            <div class="text-2xl font-bold font-mono text-zinc-800 dark:text-white">
                                {{ $documentRequest->tracking_code }}
                            </div>
                        </div>
                        
                        {{-- Status Badge --}}
                        @php
                            $statusColor = match($documentRequest->status) {
                                'pending' => 'zinc',
                                'processing' => 'yellow',
                                'ready_for_pickup' => 'blue',
                                'completed' => 'green',
                                'rejected' => 'red',
                                default => 'zinc',
                            };
                        @endphp
                        <flux:badge color="{{ $statusColor }}" size="lg" inset="top bottom">
                            {{ ucfirst(str_replace('_', ' ', $documentRequest->status)) }}
                        </flux:badge>
                    </div>

                    <flux:separator class="my-4" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:label>Document Type</flux:label>
                            <div class="font-medium">{{ $documentRequest->type->name }}</div>
                        </div>
                        <div>
                            <flux:label>Fee</flux:label>
                            <div class="font-medium">₱{{ number_format($documentRequest->type->fee, 2) }}</div>
                        </div>
                        <div>
                            <flux:label>Date Requested</flux:label>
                            <div class="font-medium">{{ $documentRequest->created_at->format('F j, Y h:i A') }}</div>
                        </div>
                        <div>
                            <flux:label>Purpose</flux:label>
                            <div class="font-medium">{{ $documentRequest->purpose }}</div>
                        </div>
                    </div>

                    @if($documentRequest->remarks)
                        <flux:separator class="my-4" />
                        <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <flux:label>Admin Remarks</flux:label>
                            <p class="text-zinc-700 dark:text-zinc-300 mt-1">
                                {{ $documentRequest->remarks }}
                            </p>
                        </div>
                    @endif
                </flux:card>
            </div>

            {{-- Right Column: Attachments --}}
            <div class="lg:col-span-1">
                <flux:card>
                    <flux:heading size="md" class="mb-4">Attachments</flux:heading>
                    
                    @if($documentRequest->attachments->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($documentRequest->attachments as $file)
                                <div class="flex items-center justify-between p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        {{-- File Icon based on type --}}
                                        @if(str_contains($file->file_type, 'image'))
                                            <flux:icon name="photo" class="text-blue-500 shrink-0" />
                                        @elseif(str_contains($file->file_type, 'pdf'))
                                            <flux:icon name="document-text" class="text-red-500 shrink-0" />
                                        @else
                                            <flux:icon name="paper-clip" class="text-zinc-500 shrink-0" />
                                        @endif
                                        
                                        <div class="truncate text-sm font-medium">
                                            {{ $file->file_name ?? 'Attachment ' . $loop->iteration }}
                                        </div>
                                    </div>
                                    
                                    {{-- Download Link --}}
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                                        <flux:icon name="arrow-down-tray" size="sm" />
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-zinc-500 text-sm italic">No attachments uploaded.</div>
                    @endif
                </flux:card>
            </div>

        </div>
    </div>

    {{-- Cancel Request Modal --}}
    <flux:modal name="cancel-request-modal" class="min-w-[20rem] md:min-w-[25rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Cancel Request?</flux:heading>
                <flux:subheading>
                    Are you sure you want to cancel this request? This action cannot be undone.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Keep Request</flux:button>
                </flux:modal.close>

                <form action="{{ route('resident.requests.destroy', $documentRequest->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger">Yes, Cancel it</flux:button>
                </form>
            </div>
        </div>
    </flux:modal>

</x-layouts::app>