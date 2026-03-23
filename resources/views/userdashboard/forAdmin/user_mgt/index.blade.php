<x-layouts::app :title="__('Account Management')">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Users</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                name: true,
                email: true,
                role: true,
                status: true,
                date: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">User Management</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Manage account holders and their permissions.</flux:subheading>
            </div>
            
            <flux:button href="{{ route('users.create') }}" variant="primary" icon="plus">Add User</flux:button>
        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" x-on:change="$el.submit()" class="flex flex-col gap-4">
            
            {{-- User Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Role Filter Dropdown --}}
                <flux:dropdown>
                    @php $currentRoles = (array) request('roles', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentRoles) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentRoles) ? count($currentRoles) . ' Roles' : 'Role' }}
                    </flux:badge>
                    
                    <flux:menu class="w-48 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Role</flux:heading>
                        <flux:checkbox name="roles[]" value="admin" label="Admin" :checked="in_array('admin', $currentRoles)" />
                        <flux:checkbox name="roles[]" value="official" label="Barangay Official" :checked="in_array('official', $currentRoles)" />
                        <flux:checkbox name="roles[]" value="resident" label="Resident" :checked="in_array('resident', $currentRoles)" />
                    </flux:menu>
                </flux:dropdown>

                {{-- Verification Status Filter Dropdown --}}
                <flux:dropdown>
                    @php $currentStatuses = (array) request('verification_statuses', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentStatuses) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentStatuses) ? count($currentStatuses) . ' Statuses' : 'Verification' }}
                    </flux:badge>
                    
                    <flux:menu class="w-56 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Account Status</flux:heading>
                        <flux:checkbox name="verification_statuses[]" value="pending" label="Pending" :checked="in_array('pending', $currentStatuses)" />
                        <flux:checkbox name="verification_statuses[]" value="verified" label="Verified" :checked="in_array('verified', $currentStatuses)" />
                        <flux:checkbox name="verification_statuses[]" value="rejected" label="Rejected" :checked="in_array('rejected', $currentStatuses)" />
                    </flux:menu>
                </flux:dropdown>
                
                {{-- Clear Filters Button --}}
                @if(request()->hasAny(['roles', 'verification_statuses', 'search']))
                    <flux:button href="{{ route('users.index') }}" size="sm" variant="subtle" icon="x-mark">
                        Clear
                    </flux:button>
                @endif
            </div>

            {{-- Toolbar: Search & Actions --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Search Input --}}
                <div class="flex-1 max-w-sm">
                    <flux:input 
                        name="search" 
                        value="{{ request('search') }}" 
                        icon="magnifying-glass" 
                        placeholder="Search users by name or email..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.name" label="Name & Status" x-on:change.stop />
                            <flux:checkbox x-model="cols.email" label="Email" x-on:change.stop />
                            <flux:checkbox x-model="cols.role" label="Role" x-on:change.stop />
                            <flux:checkbox x-model="cols.status" label="Verification" x-on:change.stop />
                            <flux:checkbox x-model="cols.date" label="Date Added" x-on:change.stop />
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </form>

        {{-- Table Container --}}
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                    <flux:table.column x-show="cols.name">Name</flux:table.column>
                    <flux:table.column x-show="cols.email">Email</flux:table.column>
                    <flux:table.column x-show="cols.role">Role</flux:table.column>
                    <flux:table.column x-show="cols.status">Status</flux:table.column>
                    <flux:table.column x-show="cols.date">Date Added</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($users as $user)
                        <flux:table.row 
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('users.show', $user->id) }}'"
                        >
                            <flux:table.cell x-show="cols.name">
                                <div class="flex items-center gap-3">
                                    <flux:avatar circle src="{{ $user->resident->user?->profile_photo_url ?? '' }}" initials="{{ substr($user->resident->fname ?? 'U', 0, 1) }}" />
                                        
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            @if($user->resident)
                                                {{ $user->resident->fname }} {{ $user->resident->mname }} {{ $user->resident->lname }} {{ $user->resident->suffix }}
                                            @else
                                                <span class="text-zinc-500 italic">No resident linked</span>
                                            @endif
                                        </div>
                                        
                                        {{-- Online/Offline Status Indicator --}}
                                        <div class="flex items-center gap-1.5 mt-0.5 text-xs">
                                            @php
                                                $isOnline = \Illuminate\Support\Facades\DB::table('sessions')
                                                    ->where('user_id', $user->id)
                                                    ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                                                    ->exists();
                                            @endphp

                                            @if($isOnline)
                                                <span class="relative flex h-2 w-2">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                                </span>
                                                <span class="text-green-600 dark:text-green-400 font-medium">Online</span>
                                            @else
                                                <span class="inline-flex rounded-full h-2 w-2 bg-zinc-300 dark:bg-zinc-600"></span>
                                                <span class="text-zinc-500 dark:text-zinc-400">Offline</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.email" class="text-zinc-600 dark:text-zinc-300">
                                {{ $user->email }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.role">
                                @php
                                    $colors = [
                                        'admin' => 'violet',
                                        'official' => 'blue',
                                        'resident' => 'green',
                                    ];
                                    $color = $colors[$user->role] ?? 'zinc';
                                @endphp
                                <flux:badge rounded size="sm" :color="$color">{{ ucfirst($user->role) }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.status">
                                @if($user->verification_status === 'verified') 
                                    <flux:badge rounded size="sm" color="green" inset="top bottom">Verified</flux:badge>
                                @elseif($user->verification_status === 'pending')
                                    <flux:badge rounded size="sm" color="yellow" inset="top bottom">Pending</flux:badge>
                                @else
                                    <flux:badge rounded size="sm" color="red" inset="top bottom">Rejected</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.date" class="text-zinc-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end" onclick="event.stopPropagation()">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />

                                    <flux:menu>
                                        <flux:menu.item href="{{ route('users.show', $user->id) }}" icon="eye">
                                            View Details
                                        </flux:menu.item>

                                        <flux:menu.item href="{{ route('users.edit', $user->id) }}" icon="pencil-square">
                                            Edit
                                        </flux:menu.item>

                                        <flux:menu.separator />

                                        {{-- Updated Delete Trigger --}}
                                        <flux:modal.trigger name="delete-user-{{ $user->id }}">
                                            <flux:menu.item icon="trash" variant="danger">
                                                Delete
                                            </flux:menu.item>
                                        </flux:modal.trigger>

                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.users class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    @if(request()->hasAny(['search', 'roles', 'verification_statuses']))
                                        <p>No users found matching your filters.</p>
                                    @else
                                        <p>No account holders found.</p>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endempty
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    {{-- Modals Loop --}}
    @foreach ($users as $userModal)
        <x-delete-modal 
            name="delete-user-{{ $userModal->id }}" 
            action="{{ route('users.destroy', $userModal->id) }}"
        >
            This will permanently delete the account for 
            <strong>
                @if($userModal->resident)
                    {{ $userModal->resident->fname }} {{ $userModal->resident->lname }}
                @else
                    {{ $userModal->email }}
                @endif
            </strong>. 
            This action cannot be undone.
        </x-delete-modal>
    @endforeach

</x-layouts::app>