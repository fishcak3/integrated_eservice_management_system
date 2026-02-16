<x-layouts::app :title="__('Dashboard')">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        <div class="grid auto-rows-min gap-6 md:grid-cols-3">
            
            <flux:card class="space-y-2">
                <div class="flex items-center justify-between">
                    <flux:subheading>Total Residents</flux:subheading>
                    <div class="rounded-full bg-zinc-100 p-2 dark:bg-zinc-800">
                        <flux:icon.users class="size-5 text-zinc-500" />
                    </div>
                </div>
                <flux:heading size="xl">{{ number_format($totalResidents) }}</flux:heading>
                <div class="flex items-center gap-2 text-sm text-zinc-500">
                    <flux:icon.check-circle class="size-4 text-green-500" />
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($totalVoters) }}</span> Voters
                </div>
            </flux:card>

            <flux:card class="space-y-2">
                <div class="flex items-center justify-between">
                    <flux:subheading>Pending Requests</flux:subheading>
                    <div class="rounded-full bg-amber-50 p-2 dark:bg-amber-900/20">
                        <flux:icon.document-text class="size-5 text-amber-600 dark:text-amber-500" />
                    </div>
                </div>
                <flux:heading size="xl">{{ $pendingDocs }}</flux:heading>
                <div class="flex items-center gap-2 text-sm text-zinc-500">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $todaysDocs }}</span> received today
                </div>
            </flux:card>

            <flux:card class="space-y-2">
                <div class="flex items-center justify-between">
                    <flux:subheading>Active Complaints</flux:subheading>
                    <div class="rounded-full bg-red-50 p-2 dark:bg-red-900/20">
                        <flux:icon.scale class="size-5 text-red-600 dark:text-red-500" />
                    </div>
                </div>
                <flux:heading size="xl">{{ $activeComplaints }}</flux:heading>
                <div class="text-sm text-zinc-500">
                    Requires attention
                </div>
            </flux:card>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            
            <div class="lg:col-span-2">
                <flux:card class="h-full space-y-4" x-data="{ activeTab: 'documents' }">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        
                        <div class="flex w-fit items-center rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
                            <button 
                                @click="activeTab = 'documents'"
                                class="rounded-md px-3 py-1.5 text-sm font-medium transition-all"
                                :class="activeTab === 'documents' 
                                    ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-600 dark:text-zinc-100' 
                                    : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Document Requests
                            </button>
                            <button 
                                @click="activeTab = 'complaints'"
                                class="rounded-md px-3 py-1.5 text-sm font-medium transition-all"
                                :class="activeTab === 'complaints' 
                                    ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-600 dark:text-zinc-100' 
                                    : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Complaint Files
                            </button>
                        </div>

                        <flux:button size="sm" variant="ghost" icon="arrow-right">View All</flux:button>
                    </div>

                    <div x-show="activeTab === 'documents'">
                        <flux:table>
                            <thead>
                                <tr>
                                    <th>Tracking</th>
                                    <th>Requestor</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRequests as $request)
                                    <tr class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                                        <td class="whitespace-nowrap py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $request->tracking_code }}
                                        </td>
                                        <td class="whitespace-nowrap py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $request->requestor_name ?? $request->user->name ?? 'N/A' }}
                                        </td>
                                        <td class="whitespace-nowrap py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $request->documentType->name ?? 'Document' }}
                                        </td>
                                        <td class="whitespace-nowrap py-3 text-sm">
                                            <flux:badge size="sm" :color="$request->status === 'pending' ? 'yellow' : ($request->status === 'completed' ? 'green' : 'zinc')">
                                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                            </flux:badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-sm text-zinc-500">No recent requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </flux:table>
                    </div>

                    <div x-show="activeTab === 'complaints'" style="display: none;">
                        <flux:table>
                            <thead>
                                <tr>
                                    <th>Case No.</th>
                                    <th>Complainant</th>
                                    <th>Nature</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentComplaints ?? [] as $complaint)
                                    <tr class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                                        <td class="whitespace-nowrap py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $complaint->case_no ?? $complaint->id }}
                                        </td>
                                        <td class="whitespace-nowrap py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $complaint->complainant_name ?? 'N/A' }}
                                        </td>
                                        <td class="whitespace-nowrap py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $complaint->nature ?? 'Complaint' }}
                                        </td>
                                        <td class="whitespace-nowrap py-3 text-sm">
                                            <flux:badge size="sm" :color="$complaint->status === 'pending' ? 'yellow' : ($complaint->status === 'solved' ? 'green' : 'red')">
                                                {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                            </flux:badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-sm text-zinc-500">No active complaints found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </flux:table>
                    </div>

                </flux:card>
            </div>

            <div class="lg:col-span-1">
                <flux:card class="flex h-full flex-col">
                    <flux:heading size="lg" class="mb-4">Demographics</flux:heading>
                    
                    <div 
                        x-data="{
                            init() {
                                let options = {
                                    series: [
                                        {{ $sectoralStats['seniors'] }}, 
                                        {{ $sectoralStats['pwd'] }}, 
                                        {{ $sectoralStats['solo_parents'] }}, 
                                        {{ $sectoralStats['4ps'] }}
                                    ],
                                    chart: {
                                        type: 'pie',
                                        height: 320,
                                        fontFamily: 'inherit',
                                        toolbar: { show: false }
                                    },
                                    labels: ['Seniors', 'PWDs', 'Solo Parent', '4Ps'],
                                    colors: ['#9333ea', '#4f46e5', '#db2777', '#16a34a'], 
                                    legend: {
                                        position: 'bottom',
                                        horizontalAlign: 'center',
                                        offsetY: 8
                                    },
                                    dataLabels: {
                                        enabled: true,
                                        dropShadow: { enabled: false }
                                    },
                                    stroke: {
                                        show: false
                                    }
                                };
                                
                                let chart = new ApexCharts(this.$refs.chart, options);
                                chart.render();
                            }
                        }" 
                        class="flex-1"
                    >
                        <div x-ref="chart" class="flex h-full items-center justify-center"></div>
                    </div>
                </flux:card>
            </div>

        </div>
    </div>
</x-layouts::app>