<x-layouts::app :title="__('Official Dashboard')">
    
    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Welcome back, {{ auth()->user()->name ?? 'Official' }}!</flux:heading>
            <flux:subheading>Here is an overview of the barangay and your assigned tasks for today.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('official.residents.create') }}" variant="primary" icon="user-plus">New Resident</flux:button>
        </div>
    </div>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- STATS GRID --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            
            {{-- 1. Residents --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>Residents</flux:subheading>
                    <div class="rounded-full bg-blue-50 p-2 dark:bg-blue-900/20">
                        <flux:icon.users class="size-5 text-blue-600 dark:text-blue-500" />
                    </div>
                </div>
                <flux:heading size="xl">{{ number_format($stats['residents']['total']) }}</flux:heading>
                <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500">
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['residents']['active']) }}</span> Active</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['residents']['pending']) }}</span> Pending</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['residents']['transferred']) }}</span> Moved</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['residents']['deceased']) }}</span> Deceased</div>
                </div>
            </flux:card>

            {{-- 2. Households --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>Households</flux:subheading>
                    <div class="rounded-full bg-indigo-50 p-2 dark:bg-indigo-900/20">
                        <flux:icon.building-office-2 class="size-5 text-indigo-600 dark:text-indigo-500" />
                    </div>
                </div>
                
                <flux:heading size="xl">{{ number_format($stats['households']['total']) }}</flux:heading>

                {{-- Added Details Section --}}
                <div class="pt-4 mt-2 flex items-center justify-between text-sm">
                    <div class="flex flex-col">
                        <span class="font-medium text-gray-700 dark:text-gray-300">
                            {{ number_format($stats['households']['total_members']) }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Total Members</span>
                    </div>
                    
                    <div class="flex flex-col text-right">
                        <span class="font-medium text-gray-700 dark:text-gray-300">
                            {{ $stats['households']['avg_members'] }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Avg. Size</span>
                    </div>
                </div>
            </flux:card>

            {{-- 3. Document Requests --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>Document Requests</flux:subheading>
                    <div class="rounded-full bg-amber-50 p-2 dark:bg-amber-900/20">
                        <flux:icon.document-text class="size-5 text-amber-600 dark:text-amber-500" />
                    </div>
                </div>
                <flux:heading size="xl">{{ number_format($stats['requests']['total']) }}</flux:heading>
                <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500">
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['requests']['pending']) }}</span> Pending</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['requests']['processing']) }}</span> Processing</div>
                    <div class="col-span-2"><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['requests']['ready']) }}</span> Ready for Pickup</div>
                </div>
            </flux:card>

            {{-- 4. Complaints / Blotters --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>Active Complaints</flux:subheading>
                    <div class="rounded-full bg-red-50 p-2 dark:bg-red-900/20">
                        <flux:icon.scale class="size-5 text-red-600 dark:text-red-500" />
                    </div>
                </div>
                <flux:heading size="xl">{{ number_format($stats['complaints']['active']) }}</flux:heading>
                <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500">
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['complaints']['pending']) }}</span> Pending</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['complaints']['processing']) }}</span> Processing</div>
                    <div class="col-span-2"><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($stats['complaints']['scheduled']) }}</span> Hearing Scheduled</div>
                </div>
            </flux:card>

        </div>

        {{-- MAIN CONTENT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- LEFT COLUMN: Assigned Tasks --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Assigned Document Requests --}}
                <flux:card>
                    <div class="flex items-center justify-between mb-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                        <flux:heading size="md">My Assigned Document Requests</flux:heading>
                        <flux:button href="{{ route('official.documents.index') }}" size="sm" variant="ghost">View All</flux:button>
                    </div>

                    @if($assignedRequests->count() > 0)
                        <div class="space-y-3">
                            @foreach($assignedRequests as $request)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $request->documentType->name ?? 'Document Request' }}
                                        </div>
                                        <div class="text-xs text-zinc-500">
                                            Code: {{ $request->tracking_code }} &bull; Requestor: {{ $request->requestor_name ?? 'Resident' }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <flux:badge color="{{ $request->status === 'pending' ? 'amber' : 'blue' }}" size="sm">
                                            {{ ucfirst($request->status) }}
                                        </flux:badge>
                                        <flux:button href="{{ route('official.documents.process', $request->id) }}" size="xs" variant="primary">Process</flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-zinc-500 text-sm italic">
                            You have no pending document requests assigned to you.
                        </div>
                    @endif
                </flux:card>

                {{-- Assigned Complaints --}}
                <flux:card>
                    <div class="flex items-center justify-between mb-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                        <flux:heading size="md">My Assigned Complaints / Blotters</flux:heading>
                        <flux:button href="{{ route('official.complaints.index') }}" size="sm" variant="ghost">View All</flux:button>
                    </div>

                    @if($assignedComplaints->count() > 0)
                        <div class="space-y-3">
                            @foreach($assignedComplaints as $complaint)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $complaint->type->name ?? 'Complaint' }}
                                        </div>
                                        <div class="text-xs text-zinc-500">
                                            Case #{{ $complaint->case_number }} &bull; Vs. {{ $complaint->respondent_name }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <flux:badge color="red" size="sm">
                                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                        </flux:badge>
                                        <flux:button href="{{ route('official.complaints.show', $complaint->id) }}" size="xs" variant="subtle">View Case</flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-zinc-500 text-sm italic">
                            You have no active complaints assigned to you.
                        </div>
                    @endif
                </flux:card>
            </div>

            {{-- RIGHT COLUMN: Announcements & Shortcuts --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Recent Announcements --}}
                <flux:card>
                    <div class="flex items-center justify-between mb-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                        <flux:heading size="md">Active Announcements</flux:heading>
                    </div>

                    @if($recentAnnouncements->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentAnnouncements as $announcement)
                                <div class="group cursor-pointer">
                                    <div class="text-xs text-zinc-500 mb-1">{{ $announcement->publish_at->format('M d, Y') }}</div>
                                    <a href="{{ route('official.announcements.show', $announcement->slug) }}" class="font-medium text-sm text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                                        {{ Str::limit($announcement->title, 50) }}
                                    </a>
                                </div>
                                @if(!$loop->last) <flux:separator class="my-3" /> @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-zinc-500 text-sm italic">
                            No active announcements.
                        </div>
                    @endif
                    
                    <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button href="{{ route('official.announcements.create') }}" variant="subtle" size="sm" class="w-full" icon="plus">Post Announcement</flux:button>
                    </div>
                </flux:card>

            </div>
        </div>
    </div>
</x-layouts::app>