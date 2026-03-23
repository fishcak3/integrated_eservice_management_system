<x-layouts::app :title="__('Request Details')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('resident.requests.index') }}">Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Request Details</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0 sm:p-4">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-2">
            <div>
                <flux:heading size="xl">Request Details</flux:heading>
                <flux:subheading>View the status and information of your document request.</flux:subheading>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                
                {{-- Only show Edit/Cancel if pending --}}
                @if($documentRequest->status === 'pending')
                    <flux:button href="{{ route('resident.requests.edit', $documentRequest->id) }}" icon="pencil-square">
                        Edit
                    </flux:button>
                    
                    <flux:modal.trigger name="cancel-request-modal">
                        <flux:button variant="danger" icon="trash">Cancel</flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                <flux:card class="!p-1"> {{-- Removed default padding to allow edge-to-edge banner --}}
                    
                    {{-- Tracking Code Banner --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-zinc-50 dark:bg-zinc-800/50 rounded-t-xl border-b border-zinc-200 dark:border-zinc-700">
                        <div>
                            <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Tracking Code</div>
                            <div class="text-3xl font-bold font-mono text-zinc-900 dark:text-white">
                                {{ $documentRequest->tracking_code }}
                            </div>
                        </div>
                        
                        {{-- Status Badge --}}
                        @php
                            $statusColor = match($documentRequest->status) {
                                'pending' => 'yellow',
                                'processing' => 'blue',
                                'ready_for_pickup' => 'green',
                                'completed' => 'zinc',
                                'rejected', 'cancelled' => 'red',
                                default => 'zinc',
                            };
                        @endphp
                        <flux:badge color="{{ $statusColor }}" size="lg" inset="top bottom" class="px-4 py-2 text-sm shadow-sm">
                            {{ ucfirst(str_replace('_', ' ', $documentRequest->status)) }}
                        </flux:badge>
                    </div>

                    {{-- ---------------------------------------------------------------- --}}
                    {{-- VISUAL STATUS STEPPER --}}
                    {{-- ---------------------------------------------------------------- --}}
                    <div class="px-6 py-8 border-b border-zinc-200 dark:border-zinc-700 w-full overflow-hidden">
                        @php
                            $steps = [
                                'pending' => ['label' => 'Pending', 'icon' => 'clock'],
                                'processing' => ['label' => 'Processing', 'icon' => 'arrow-path'],
                                'ready_for_pickup' => ['label' => 'Ready for Pickup', 'icon' => 'inbox'],
                                'completed' => ['label' => 'Completed', 'icon' => 'check-circle'],
                            ];

                            $flow = array_keys($steps);
                            $currentStatus = $documentRequest->status;
                            $currentIndex = array_search($currentStatus, $flow);
                            $isError = in_array($currentStatus, ['rejected', 'cancelled']);
                        @endphp

                        <div class="flex items-center justify-between w-full max-w-2xl mx-auto mb-4 mt-2">
                            @foreach($steps as $key => $step)
                                @php
                                    $stepIndex = $loop->index;
                                    
                                    // Logic for completed vs active states
                                    $isCompleted = $currentIndex !== false && $currentIndex > $stepIndex && !$isError;
                                    $isCurrent = $currentStatus === $key;

                                    if ($isCompleted) {
                                        $circleClass = 'bg-green-500 border-green-500 text-white';
                                    } elseif ($isCurrent) {
                                        $circleClass = 'bg-blue-100 dark:bg-blue-500/20 border-blue-200 dark:border-blue-500/30 text-blue-600 dark:text-blue-400 ring-4 ring-blue-50 dark:ring-blue-500/10';
                                    } else {
                                        $circleClass = 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-400 dark:text-zinc-600';
                                    }
                                @endphp

                                {{-- Step Item --}}
                                <div class="relative flex flex-col items-center z-10 shrink-0">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full border-2 flex items-center justify-center transition-all duration-300 {{ $circleClass }}">
                                        @if($isCompleted)
                                            <flux:icon name="check" class="w-5 h-5 sm:w-6 sm:h-6" />
                                        @else
                                            <flux:icon name="{{ $step['icon'] }}" class="w-5 h-5 sm:w-6 sm:h-6" />
                                        @endif
                                    </div>
                                    
                                    {{-- Absolute positioned label so it doesn't affect flex spacing --}}
                                    <div class="absolute top-12 sm:top-14 mt-1 text-center w-20 sm:w-max sm:whitespace-nowrap text-[10px] sm:text-xs font-medium {{ $isCurrent ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 dark:text-zinc-400' }}">
                                        {{ $step['label'] }}
                                    </div>
                                </div>

                                {{-- Connecting Line (Except for last item) --}}
                                @if(!$loop->last)
                                    @php
                                        // Line is colored if the NEXT step has been reached
                                        $lineActive = $currentIndex !== false && $currentIndex > $stepIndex && !$isError;
                                    @endphp
                                    <div class="flex-1 h-[2px] mx-2 sm:mx-4 transition-all duration-300 {{ $lineActive ? 'bg-green-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    {{-- ---------------------------------------------------------------- --}}


                    {{-- Request Details Grid --}}
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-y-8 gap-x-6">
                        
                        <div class="flex gap-3 items-start">
                            <div class="p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                <flux:icon name="document-text" class="w-5 h-5" />
                            </div>
                            <div>
                                <flux:label>Document Type</flux:label>
                                <div class="font-semibold text-zinc-900 dark:text-white mt-0.5">{{ $documentRequest->documentType->name }}</div>
                            </div>
                        </div>

                        <div class="flex gap-3 items-start">
                            <div class="p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                <flux:icon name="banknotes" class="w-5 h-5" />
                            </div>
                            <div>
                                <flux:label>Fee</flux:label>
                                <div class="font-semibold text-zinc-900 dark:text-white mt-0.5">₱{{ number_format($documentRequest->documentType->fee, 2) }}</div>
                            </div>
                        </div>

                        <div class="flex gap-3 items-start">
                            <div class="p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                <flux:icon name="calendar" class="w-5 h-5" />
                            </div>
                            <div>
                                <flux:label>Date Requested</flux:label>
                                <div class="font-semibold text-zinc-900 dark:text-white mt-0.5">{{ $documentRequest->created_at->format('M j, Y • h:i A') }}</div>
                            </div>
                        </div>

                        <div class="flex gap-3 items-start">
                            <div class="p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                <flux:icon name="briefcase" class="w-5 h-5" />
                            </div>
                            <div>
                                <flux:label>Purpose</flux:label>
                                <div class="font-semibold text-zinc-900 dark:text-white mt-0.5">{{ $documentRequest->purpose }}</div>
                            </div>
                        </div>

                    </div>

                    {{-- Admin Remarks Callout --}}
                    @if($documentRequest->remarks)
                        <div class="mx-6 mb-6">
                            <div class="border-l-4 border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 p-4 rounded-r-xl">
                                <div class="flex items-center gap-2 text-indigo-800 dark:text-indigo-300 mb-2">
                                    <flux:icon name="chat-bubble-left-ellipsis" class="w-5 h-5" />
                                    <span class="font-semibold text-sm">Note from Admin</span>
                                </div>
                                <p class="text-sm text-indigo-900 dark:text-indigo-200 leading-relaxed">
                                    {{ $documentRequest->remarks }}
                                </p>
                            </div>
                        </div>
                    @endif
                </flux:card>
            </div>

            {{-- Right Column: Attachments --}}
            <div class="lg:col-span-1">
                <flux:card>
                    <div class="flex items-center gap-2 mb-4 text-zinc-800 dark:text-white">
                        <flux:icon name="paper-clip" class="w-5 h-5" />
                        <flux:heading size="md">Attachments</flux:heading>
                    </div>
                    
                    @if($documentRequest->attachments->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($documentRequest->attachments as $file)
                                <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="group flex items-center justify-between p-3 border border-zinc-200 dark:border-zinc-700 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-600 transition-all duration-200 cursor-pointer">
                                    
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        {{-- File Icon block --}}
                                        <div class="p-2 rounded-lg bg-white dark:bg-zinc-900 shadow-sm border border-zinc-100 dark:border-zinc-800 group-hover:scale-105 transition-transform duration-200 shrink-0">
                                            @if(str_contains($file->file_type, 'image'))
                                                <flux:icon name="photo" class="text-blue-500 w-5 h-5" />
                                            @elseif(str_contains($file->file_type, 'pdf'))
                                                <flux:icon name="document-text" class="text-red-500 w-5 h-5" />
                                            @else
                                                <flux:icon name="document" class="text-zinc-500 w-5 h-5" />
                                            @endif
                                        </div>
                                        
                                        <div class="truncate text-sm font-medium text-zinc-700 dark:text-zinc-300 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
                                            {{ $file->file_name ?? 'Attachment ' . $loop->iteration }}
                                        </div>
                                    </div>
                                    
                                    {{-- Download Icon --}}
                                    <div class="text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200 transition-colors shrink-0">
                                        <flux:icon name="arrow-down-tray" size="sm" />
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center p-6 text-center border border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl bg-zinc-50 dark:bg-zinc-800/50">
                            <flux:icon name="archive-box-x-mark" class="w-8 h-8 text-zinc-400 mb-2" />
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm font-medium">No attachments uploaded</p>
                        </div>
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

            <div class="flex justify-end gap-3">
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