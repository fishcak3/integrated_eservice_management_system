<x-layouts::app :title="__('Dashboard')">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <x-slot:header>
        <flux:heading size="xl" class="font-bold">     
            Integrated E-Service Management System
        </flux:heading>
    </x-slot:header>

    {{-- MAIN DASHBOARD WRAPPER --}}
    <div class="flex flex-col flex-1 w-full h-full gap-6 p-0 rounded-xl">

        {{-- 1. ALERTS / ATTENTION SECTION (Top Priority) --}}
        @if(!empty($alerts) && count($alerts) > 0)
            <div class="flex flex-col gap-2">
                @foreach($alerts as $alert)
                    <div class="flex items-center gap-3 p-4 text-red-800 border border-red-200 rounded-lg bg-red-50 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400">
                        <flux:icon.exclamation-triangle class="shrink-0 size-5" />
                        <span class="text-sm font-medium">{{ $alert['message'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- 2. TOP SUMMARY CARDS --}}
        <div class="grid gap-6 auto-rows-min md:grid-cols-2 xl:grid-cols-4">
            
            {{-- Residents --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>Residents</flux:subheading>
                    <div class="p-2 rounded-full bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.users class="text-blue-600 size-5 dark:text-blue-500" />
                    </div>
                </div>
                <flux:heading size="xl">{{ number_format($stats['residents']['total'] ?? 0) }}</flux:heading>
                <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500">
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['residents']['active'] ?? 0 }}</span> Active</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['residents']['pending'] ?? 0 }}</span> Pending</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['residents']['transferred'] ?? 0 }}</span> Moved</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['residents']['deceased'] ?? 0 }}</span> Deceased</div>
                </div>
            </flux:card>

            {{-- Users --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>System Users</flux:subheading>
                    <div class="p-2 rounded-full bg-purple-50 dark:bg-purple-900/20">
                        <flux:icon.identification class="text-purple-600 size-5 dark:text-purple-500" />
                    </div>
                </div>
                <flux:heading size="xl">{{ number_format($stats['users']['total'] ?? 0) }}</flux:heading>
                <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500">
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['users']['verified'] ?? 0 }}</span> Verified</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['users']['pending'] ?? 0 }}</span> Pending</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['users']['admins'] ?? 0 }}</span> Admins</div>
                    <div><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $stats['users']['officials'] ?? 0 }}</span> Officials</div>
                </div>
            </flux:card>

            {{-- Documents --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>Document Requests</flux:subheading>
                    <div class="p-2 rounded-full bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.document-text class="text-amber-600 size-5 dark:text-amber-500" />
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

            {{-- Complaints --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:subheading>Complaint Cases</flux:subheading>
                    <div class="p-2 rounded-full bg-red-50 dark:bg-red-900/20">
                        <flux:icon.scale class="text-red-600 size-5 dark:text-red-500" />
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

        {{-- 3. UNIFIED MIDDLE SECTION (Demographics, Financials, Activity, Officials) --}}
        <div class="grid gap-6 lg:grid-cols-3">
            
            {{-- ========================================== --}}
            {{-- LEFT COLUMN: Charts & Activity (2/3 width) --}}
            {{-- ========================================== --}}
            <div class="flex flex-col h-full gap-6 lg:col-span-2">
                
                {{-- Top Row: Charts Grid --}}
                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Age Distribution Chart --}}
                    <flux:card class="flex flex-col">
                        <flux:heading size="lg" class="mb-4">Age Distribution</flux:heading>
                        <div x-data="{
                            chart: null,
                            init() {
                                let isDark = document.documentElement.classList.contains('dark');
                                this.chart = new ApexCharts(this.$refs.chart, {
                                    series: [{ name: 'Residents', data: [{{ $demographics['age']['children'] ?? 0 }}, {{ $demographics['age']['youth'] ?? 0 }}, {{ $demographics['age']['adults'] ?? 0 }}, {{ $demographics['age']['seniors'] ?? 0 }}] }],
                                    chart: { type: 'bar', height: 280, fontFamily: 'inherit', background: 'transparent', toolbar: { show: false } },
                                    theme: { mode: isDark ? 'dark' : 'light' },
                                    xaxis: { categories: ['0-12', '13-17', '18-59', '60+'] },
                                    colors: ['#3b82f6'],
                                    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                                    dataLabels: { enabled: false }
                                });
                                this.chart.render();

                                let observer = new MutationObserver(() => {
                                    let darkNow = document.documentElement.classList.contains('dark');
                                    this.chart.updateOptions({ theme: { mode: darkNow ? 'dark' : 'light' } });
                                });
                                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                            }
                        }">
                            <div x-ref="chart"></div>
                        </div>
                    </flux:card>

                    {{-- Sectoral Breakdown Chart --}}
                    <flux:card class="flex flex-col">
                        <flux:heading size="lg" class="mb-4">Sectoral Groups</flux:heading>
                        <div x-data="{
                            chart: null,
                            init() {
                                let isDark = document.documentElement.classList.contains('dark');
                                this.chart = new ApexCharts(this.$refs.chart, {
                                    series: [{{ $demographics['sectoral']['solo_parent'] ?? 0 }}, {{ $demographics['sectoral']['ofw'] ?? 0 }}, {{ $demographics['sectoral']['pwd'] ?? 0 }}, {{ $demographics['sectoral']['4ps'] ?? 0 }}, {{ $demographics['sectoral']['unemployed'] ?? 0 }}, {{ $demographics['sectoral']['osy'] ?? 0 }}],
                                    chart: { type: 'donut', height: 280, fontFamily: 'inherit', background: 'transparent' },
                                    theme: { mode: isDark ? 'dark' : 'light' },
                                    labels: ['Solo Parent', 'OFW', 'PWD', '4Ps', 'Unemployed', 'OSY'],
                                    colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                                    legend: { position: 'bottom' },
                                    stroke: { show: true, colors: isDark ? ['#27272a'] : ['#ffffff'] },
                                    plotOptions: { pie: { dataLabels: { minAngleToShowLabel: 0 } } }
                                });
                                this.chart.render();

                                let observer = new MutationObserver(() => {
                                    let darkNow = document.documentElement.classList.contains('dark');
                                    this.chart.updateOptions({
                                        theme: { mode: darkNow ? 'dark' : 'light' },
                                        stroke: { colors: darkNow ? ['#27272a'] : ['#ffffff'] }
                                    });
                                });
                                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                            }
                        }">
                            <div x-ref="chart"></div>
                        </div>
                    </flux:card>
                </div>

                {{-- Middle Row: Request Activity Tabs --}}
                <flux:card class="flex flex-col flex-1 space-y-4" x-data="{ activeTab: 'documents' }">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center p-1 rounded-lg w-fit bg-zinc-100 dark:bg-zinc-800">
                            <button @click="activeTab = 'documents'" class="rounded-md px-3 py-1.5 text-sm font-medium transition-all" :class="activeTab === 'documents' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-600 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                                Recent Documents
                            </button>
                            <button @click="activeTab = 'complaints'" class="rounded-md px-3 py-1.5 text-sm font-medium transition-all" :class="activeTab === 'complaints' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-600 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                                Recent Complaints
                            </button>
                        </div>
                        <flux:button x-show="activeTab === 'documents'" href="{{ route('admin.documents.index', ['type' => 'documents']) }}">
                            View All
                        </flux:button>
                        <flux:button x-show="activeTab === 'complaints'" href="{{ route('admin.complaints.index', ['type' => 'complaints']) }}">
                            View All
                        </flux:button>
                    </div>

                    {{-- Docs Tab --}}
                    <div x-show="activeTab === 'documents'" x-cloak>
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Tracking</flux:table.column>
                                <flux:table.column>Requestor</flux:table.column>
                                <flux:table.column>Type</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse($recentRequests as $request)
                                    <flux:table.row>
                                        <flux:table.cell class="font-medium">{{ $request->tracking_code }}</flux:table.cell>
                                        <flux:table.cell class="text-zinc-500">{{ $request->requestor_name }}</flux:table.cell>
                                        <flux:table.cell class="text-zinc-500">{{ $request->documentType->name ?? 'Document' }}</flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$request->status === 'pending' ? 'yellow' : 'zinc'">
                                                {{ ucfirst($request->status) }}
                                            </flux:badge>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="4" class="py-8 text-center text-zinc-500">
                                            No recent requests found.
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </div>

                    {{-- Complaints Tab --}}
                    <div x-show="activeTab === 'complaints'" x-cloak>
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Case No.</flux:table.column>
                                <flux:table.column>Type</flux:table.column>
                                <flux:table.column>Incident Date</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse($recentComplaints as $complaint)
                                    <flux:table.row>
                                        <flux:table.cell class="font-medium">{{ $complaint->case_number }}</flux:table.cell>
                                        <flux:table.cell class="text-zinc-500">{{ $complaint->type->name ?? 'Uncategorized' }}</flux:table.cell>
                                        <flux:table.cell class="text-zinc-500">{{ \Carbon\Carbon::parse($complaint->incident_date)->format('M d, Y') }}</flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$complaint->status === 'pending' ? 'yellow' : 'red'">
                                                {{ ucfirst($complaint->status) }}
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
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </flux:card>

                {{-- Bottom Row: SYSTEM ACTIVITY LOG (Moved here to share the same column) --}}
                <flux:card>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon.clock class="text-zinc-600 size-5 dark:text-zinc-400" />
                            </div>
                            <flux:heading size="lg">Recent Activity</flux:heading>
                        </div>
                    </div>
                        
                    <div class="mt-2 space-y-4">
                        @forelse($activityLogs ?? [] as $log)
                            <div class="flex items-start gap-4 pb-4 border-b border-zinc-100 last:border-0 last:pb-0 dark:border-zinc-800/50">
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm text-zinc-800 dark:text-zinc-200">
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $log->causer->name ?? 'System' }}</span>
                                        {{ $log->description }}
                                    </p>
                                    <div class="text-xs text-zinc-500">
                                        {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-sm text-center text-zinc-500">
                                No recent activity recorded.
                            </div>
                        @endforelse
                    </div>
                </flux:card>

            </div>

            {{-- ==================================================== --}}
            {{-- RIGHT COLUMN: Financials, Calendar, Officials (1/3 width) --}}
            {{-- ==================================================== --}}
            <div class="flex flex-col h-full gap-6 lg:col-span-1">
                
                {{-- Financials --}}
                <flux:card>
                    <div class="flex items-center gap-3 mb-2">
                        <flux:icon.banknotes class="text-green-600 size-5" />
                        <flux:heading size="lg">Financial Snapshot</flux:heading>
                    </div>
                    <div class="mt-4 space-y-4">
                        <div>
                            <div class="text-sm text-zinc-500">Est. Revenue (This Month)</div>
                            <div class="text-2xl font-semibold text-zinc-900 dark:text-white">₱{{ number_format($financials['est_revenue'] ?? 0, 2) }}</div>
                        </div>
                        <hr class="border-zinc-100 dark:border-zinc-800" />
                        <div>
                            <div class="text-sm text-zinc-500">Most Requested Document</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-300">{{ $financials['top_document'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                </flux:card>

                {{-- Announcements Calendar --}}
                <flux:card>
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <flux:icon.calendar/>
                            <flux:heading size="lg">Announcement Schedule</flux:heading>
                        </div>
                        <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            {{ now()->format('F Y') }}
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-2 mb-2 text-xs font-semibold tracking-wider text-center uppercase text-zinc-400">
                        <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        @foreach($calendarDays as $dayInfo)
                            @if(empty($dayInfo['day']))
                                {{-- Empty slots for alignment --}}
                                <div class="p-2"></div>
                            @else
                                @php
                                    // Filter out archived announcements to only show draft or published
                                    $activeEvents = $dayInfo['events']->whereIn('status', ['published', 'draft']);

                                    $hasPublished = $activeEvents->where('status', 'published')->isNotEmpty();
                                    $hasDraft = $activeEvents->where('status', 'draft')->isNotEmpty();

                                    $baseClass = "group relative flex flex-col items-center justify-center p-2 text-sm font-bold transition-colors rounded-lg shadow-sm cursor-pointer ";
                                    
                                    if ($hasPublished) {
                                        $colorClass = "bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20";
                                    } elseif ($hasDraft) {
                                        $colorClass = "bg-amber-50 text-amber-700 border border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 hover:bg-amber-100 dark:hover:bg-amber-500/20";
                                    } else {
                                        $colorClass = "border border-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800/50 text-zinc-700 dark:text-zinc-300 font-normal";
                                    }
                                @endphp

                                <div class="{{ $baseClass . $colorClass }}">
                                    {{ $dayInfo['day'] }}
                                    
                                    @if($hasPublished)
                                        <span class="absolute bottom-1 w-1 h-1 bg-emerald-500 rounded-full"></span>
                                    @elseif($hasDraft)
                                        <span class="absolute bottom-1 w-1 h-1 bg-amber-500 rounded-full"></span>
                                    @endif

                                    {{-- Use the filtered $activeEvents instead of all events --}}
                                    @if($activeEvents->isNotEmpty())
                                        <div class="absolute z-50 flex-col hidden w-48 p-2 mb-2 text-xs text-left transition-opacity -translate-x-1/2 rounded-md shadow-xl pointer-events-none bottom-full left-1/2 bg-zinc-800 text-zinc-100 dark:bg-zinc-200 dark:text-zinc-800 group-hover:flex">
                                            
                                            <div class="mb-1 font-semibold border-b border-zinc-600 dark:border-zinc-400 pb-1">
                                                {{ \Carbon\Carbon::parse($dayInfo['date'])->format('M d, Y') }}
                                            </div>
                                            
                                            <div class="flex flex-col gap-1 max-h-32 overflow-y-auto">
                                                {{-- Loop through filtered $activeEvents here as well --}}
                                                @foreach($activeEvents as $event)
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="shrink-0 size-1.5 rounded-full {{ $event->status === 'published' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                                        <span class="truncate">{{ $event->title }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <div class="absolute w-0 h-0 border-x-4 border-x-transparent border-t-[6px] border-t-zinc-800 dark:border-t-zinc-200 top-full left-1/2 -translate-x-1/2"></div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between pt-4 mt-6 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-4 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full shadow-sm"></span> Published
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 bg-amber-500 rounded-full shadow-sm"></span> Draft
                            </div>
                        </div>
                        <div class="text-xs font-semibold text-zinc-500">
                            {{ $announcements['published'] ?? 0 }} Pub / {{ $announcements['draft'] ?? 0 }} Draft
                        </div>
                    </div>
                </flux:card>

                {{-- Officials Panel --}}
                <flux:card class="flex flex-col flex-1 p-0 overflow-hidden shadow-sm">
                    
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-5 border-b border-zinc-200 dark:border-zinc-700">
                        <div>
                            <flux:heading size="lg">Current Officials</flux:heading>
                            <div class="text-sm mt-0.5 text-zinc-500 dark:text-zinc-400">Barangay Council & Appointees</div>
                        </div>
                        <div class="flex items-center justify-center size-8 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <flux:icon.users class="size-4" />
                        </div>
                    </div>

                    {{-- List --}}
                    <div class="flex-1 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($currentOfficials as $term)
                            @php
                                $resident = $term->official->resident ?? null;
                                $name = $resident ? ($resident->first_name . ' ' . $resident->last_name) : 'Unknown Official';
                                $position = $term->position->title ?? 'Official';
                            @endphp
                            
                            <div class="flex items-center justify-between px-6 py-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50 group">
                                <div class="flex items-center gap-4">
                                    <flux:avatar circle src="{{ $resident->user?->profile_photo_url ?? '' }}" initials="{{ substr($resident->first_name ?? 'U', 0, 1) }}" />
                                    
                                    {{-- Official Info --}}
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold transition-colors text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                            {{ $name }}
                                        </span>
                                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            {{ $position }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Term/Status Badge --}}
                                <div class="hidden sm:block">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold tracking-wide uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        {{ $term->election_year ?? 'Active' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            {{-- Empty State --}}
                            <div class="flex flex-col items-center justify-center px-6 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                <flux:icon.user-minus class="size-8 mx-auto mb-3 opacity-40" />
                                <p class="text-sm font-medium">No active officials configured.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Optional Footer Action --}}
                    <div class="px-6 py-3 border-t bg-zinc-50 dark:bg-zinc-800/30 border-zinc-200 dark:border-zinc-700">
                        <flux:button href="{{ route('officials.index') }}" size="xs">View Official Directory</flux:button>
                    </div>
                </flux:card>

            </div>
        </div>
    </div> 
</x-layouts::app>