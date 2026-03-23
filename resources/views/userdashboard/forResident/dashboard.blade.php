<x-layouts::app :title="__('Resident Dashboard')">

    <x-slot:header>
        <flux:sidebar.header class="font-bold text-lg">
            Integrated E-Service Management System
        </flux:sidebar.header>
    </x-slot:header>

    <div class="flex flex-col gap-1 mb-6">
        <span>Welcome back, {{ auth()->user()->name ?? 'Juan Dela Cruz' }}!</span>
        <span class="text-xs font-normal text-zinc-500">Resident, Sitio {{ auth()->user()->resident?->household->sitio ?? 'N/A' }} | Barangay {{ $global_brgy_name ?? 'N/A' }}</span>
    </div>

    {{-- Main Grid Layout --}}
    <div class="grid gap-6 lg:grid-cols-3">

        {{-- LEFT COLUMN (Stats & Tables - Spans 2 out of 3 columns) --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            {{-- Stats Row (2 columns within the left side) --}}
            <div class="grid gap-6 sm:grid-cols-2">
                {{-- Documents Stat --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:subheading>My Document</flux:subheading>
                        <div class="rounded-full bg-amber-50 p-2 dark:bg-amber-900/20">
                            <flux:icon.document-text class="size-5 text-amber-600 dark:text-amber-500" />
                        </div>
                    </div>
                    <flux:heading size="xl">{{ number_format($stats['documents']['total'] ?? 0) }}</flux:heading>
                    <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500">
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['documents']['pending'] ?? 0 }}</span> Pending</div>
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['documents']['processing'] ?? 0 }}</span> Processing</div>
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['documents']['ready'] ?? 0 }}</span> Ready</div>
                        <div><span class="font-medium text-green-600 dark:text-green-500">{{ $stats['documents']['completed_today'] ?? 0 }}</span> Done Today</div>
                    </div>
                </flux:card>
                
                {{-- Complaints Stat --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:subheading>My Complaints</flux:subheading>
                        <div class="rounded-full bg-red-50 p-2 dark:bg-red-900/20">
                            <flux:icon.scale class="size-5 text-red-600 dark:text-red-500" />
                        </div>
                    </div>
                    <flux:heading size="xl">{{ number_format($stats['complaints']['total'] ?? 0) }}</flux:heading>
                    <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500">
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['complaints']['pending'] ?? 0 }}</span> Pending</div>
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['complaints']['investigating'] ?? 0 }}</span> Investigating</div>
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['complaints']['resolved'] ?? 0 }}</span> Resolved</div>
                        <div><span class="font-medium text-red-600 dark:text-red-500">{{ $stats['complaints']['high_severity'] ?? 0 }}</span> High Severity</div>
                    </div>
                </flux:card>
            </div>

            {{-- Activity Tabs (Tables) --}}
            <flux:card class="h-full space-y-4" x-data="{ activeTab: 'documents' }">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex w-fit items-center rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
                        <button @click="activeTab = 'documents'" class="rounded-md px-3 py-1.5 text-sm font-medium transition-all" :class="activeTab === 'documents' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-600 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                            Recent Documents
                        </button>
                        <button @click="activeTab = 'complaints'" class="rounded-md px-3 py-1.5 text-sm font-medium transition-all" :class="activeTab === 'complaints' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-600 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                            Recent Complaints
                        </button>
                    </div>
                    
                    {{-- Only show 'View All' buttons if the user is verified --}}
                    @if(auth()->user()->verification_status === 'verified')
                        <flux:button
                            x-show="activeTab === 'documents'"
                            variant="filled"
                            {{-- FIX: Pointed to resident route instead of admin --}}
                            href="{{ route('resident.requests.index', ['type' => 'documents']) }}">
                            View All
                        </flux:button>

                        <flux:button
                            x-show="activeTab === 'complaints'"
                            variant="filled"
                            {{-- FIX: Pointed to resident route instead of admin --}}
                            href="{{ route('resident.requests.index', ['type' => 'complaints']) }}">
                            View All
                        </flux:button>
                    @endif
                </div>

                {{-- Docs Tab --}}
                <div x-show="activeTab === 'documents'" x-cloak>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column align="center">Tracking No.</flux:table.column>
                            <flux:table.column align="center">Type</flux:table.column>
                            <flux:table.column align="center">Status</flux:table.column>
                        </flux:table.columns>
                        
                        <flux:table.rows>
                            @if(auth()->user()->verification_status !== 'verified')
                                {{-- Unverified State Message --}}
                                <flux:table.row>
                                    <flux:table.cell colspan="3" class="py-12 text-center bg-zinc-50/50 dark:bg-zinc-800/50">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <flux:icon.lock-closed class="size-6 text-red-400 dark:text-red-500" />
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Service Locked</p>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Please verify your account to view and track your document requests.</p>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @else
                                {{-- Verified State: Show actual data --}}
                                @forelse($recentRequests ?? [] as $request)
                                    <flux:table.row>
                                        <flux:table.cell align="center" class="font-medium">{{ $request->tracking_code }}</flux:table.cell>
                                        <flux:table.cell align="center" class="text-zinc-500">{{ $request->documentType->name ?? 'Document' }}</flux:table.cell>
                                        <flux:table.cell align="center">
                                            <flux:badge size="sm" :color="$request->status === 'pending' ? 'yellow' : 'zinc'">
                                                {{ ucfirst($request->status) }}
                                            </flux:badge>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="3" class="py-8 text-center text-zinc-500">
                                            No recent requests found.
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            @endif
                        </flux:table.rows>
                    </flux:table>
                </div>

                {{-- Complaints Tab --}}
                <div x-show="activeTab === 'complaints'" x-cloak style="display: none;">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column align="center">Case No.</flux:table.column>
                            <flux:table.column align="center">Type</flux:table.column>
                            <flux:table.column align="center">Incident Date</flux:table.column>
                            <flux:table.column align="center">Status</flux:table.column>
                        </flux:table.columns>
                        
                        <flux:table.rows>
                            @if(auth()->user()->verification_status !== 'verified')
                                {{-- Unverified State Message --}}
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="py-12 text-center bg-zinc-50/50 dark:bg-zinc-800/50">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <flux:icon.lock-closed class="size-6 text-red-400 dark:text-red-500" />
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Service Locked</p>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Please verify your account to view and track your complaints.</p>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @else
                                {{-- Verified State: Show actual data --}}
                                @forelse($recentComplaints ?? [] as $complaint)
                                    <flux:table.row>
                                        <flux:table.cell align="center" class="font-medium">{{ $complaint->case_number }}</flux:table.cell>
                                        <flux:table.cell align="center" class="text-zinc-500">{{ $complaint->type->name ?? 'Uncategorized' }}</flux:table.cell>
                                        <flux:table.cell align="center" class="text-zinc-500">{{ \Carbon\Carbon::parse($complaint->incident_date)->format('M d, Y') }}</flux:table.cell>
                                        <flux:table.cell align="center">
                                            <flux:badge size="sm" :color="$complaint->status === 'pending' ? 'yellow' : 'red'">
                                                {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                            </flux:badge>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="4" class="py-8 text-center text-zinc-500">
                                            No recent complaints found.
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            @endif
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- RIGHT COLUMN (Quick Actions & Announcements - Spans 1 column) --}}
        <div class="flex flex-col gap-6">
            
            {{-- Quick Actions --}}
            <flux:card class="space-y-4">
                <flux:subheading class="font-bold text-zinc-900 dark:text-zinc-100">Quick Actions</flux:subheading>
                <div class="flex flex-col gap-3">
                    
                    @if(auth()->user()->verification_status !== 'verified')
                
                        <flux:button href="#" variant="subtle" class="w-full flex justify-start gap-2 opacity-50 cursor-not-allowed">
                            <flux:icon.lock-closed class="size-5 text-zinc-500 dark:text-zinc-400" />
                            Request Document
                        </flux:button>

                        <flux:button href="#" variant="subtle" class="w-full flex justify-start gap-2 opacity-50 cursor-not-allowed">
                            <flux:icon.lock-closed class="size-5 text-zinc-500 dark:text-zinc-400" />
                            File a Complaint
                        </flux:button>
                                                {{-- NEW: Always-visible warning message --}}
                        <div class="rounded-md bg-red-50 p-3 border border-red-100 dark:bg-red-900/20 dark:border-red-900/50">
                            <div class="flex gap-2">
                                <flux:icon.information-circle class="size-5 text-red-600 dark:text-red-400 shrink-0" />
                                <p class="text-xs font-medium text-red-800 dark:text-red-300">
                                    Service locked. You must verify your account in Settings first.
                                </p>
                            </div>
                        </div>

                        {{-- Unverified State: Warning button + Locked action buttons --}}
                        <flux:button variant="danger" href="{{ route('profile.edit') }}" class="w-full flex justify-start gap-2">
                            <flux:icon.shield-exclamation class="size-5" />
                            Verify Account (Action Required)
                        </flux:button>

                    @else
                        {{-- Verified State: Normal action buttons --}}
                        <flux:button href="{{ route('resident.requests.create') }}" class="w-full flex justify-start gap-2">
                            <flux:icon.document-plus class="size-5 text-amber-600 dark:text-amber-500" />
                            Request Document
                        </flux:button>
                        
                        <flux:button href="{{ route('resident.complaints.create') }}" class="w-full flex justify-start gap-2">
                            <flux:icon.scale class="size-5 text-red-600 dark:text-red-500" />
                            File a Complaint
                        </flux:button>
                    @endif

                </div>
            </flux:card>

            {{-- Latest Announcements --}}
            <flux:card class="!p-0 flex flex-col overflow-hidden h-fit">
                {{-- Header --}}
                <div class="flex items-center justify-between bg-emerald-50 px-5 py-4 dark:bg-emerald-900/20">
                    <h3 class="font-bold text-zinc-900 dark:text-zinc-100">Latest Announcements</h3>
                    <a href="{{ route('resident.announcements.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200">
                        View All
                    </a>
                </div>

                {{-- List --}}
                <div class="flex flex-col divide-y divide-zinc-100 dark:divide-zinc-800">
                    
                    @forelse($recentAnnouncements ?? [] as $announcement)
                        <div class="flex gap-4 p-5">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100/50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <flux:icon.megaphone class="size-5" />
                            </div>
                            <div class="space-y-1.5 w-full">
                                <div>
                                    <h4 class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">{{ $announcement->title }}</h4>
                                    <div class="flex items-center gap-1.5 text-xs text-zinc-500 mt-0.5">
                                        <flux:icon.calendar class="size-3.5" />
                                        {{-- Use publish_at if it exists, otherwise use created_at --}}
                                        <span>{{ ($announcement->publish_at ?? $announcement->created_at)->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                                
                                {{-- Strip HTML tags (if you are using a rich text editor) and limit to 80 characters --}}
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ Str::limit(strip_tags($announcement->content), 80) }}
                                </p>
                                
                                {{-- Link to the specific announcement using its slug --}}
                                <a href="{{ route('resident.announcements.show', $announcement->slug ?? $announcement->id) }}" class="inline-flex text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Read More &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:icon.bell-slash class="size-8 mx-auto mb-3 opacity-50" />
                            No active announcements right now.
                        </div>
                    @endforelse

                </div>
            </flux:card>
        </div>

    </div>
</x-layouts::app>