<x-layouts::app :title="__('View User Details')">
    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('users.index') }}">Users</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>View</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $user->email }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">User Profile</flux:heading>
                <flux:subheading>View account and resident details.</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('users.index') }}" variant="ghost" icon="arrow-left">Back to List</flux:button>
                <flux:button href="{{ route('users.edit', $user->id) }}" variant="primary" icon="pencil">Edit User</flux:button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- LEFT COLUMN: User Account Card --}}
            <div class="md:col-span-1 space-y-6">
                <flux:card class="text-center">
                    <div class="flex flex-col items-center">
                        <div class="relative mb-4">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile" 
                                     class="w-32 h-32 object-cover rounded-full ring-4 ring-zinc-100 dark:ring-zinc-800 shadow-md">
                            @else
                                <div class="w-32 h-32 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center ring-4 ring-zinc-100 dark:ring-zinc-800">
                                    <span class="text-4xl text-zinc-400 font-bold">{{ strtoupper(substr($user->email, 0, 1)) }}</span>
                                </div>
                            @endif
                            
                            {{-- Active/Verified Status Badge --}}
                            <div class="absolute bottom-0 right-2">
                                @if($user->email_verified_at)
                                    <div class="bg-green-500 border-2 border-white dark:border-zinc-900 rounded-full p-1.5" title="Verified"></div>
                                @else
                                    <div class="bg-yellow-500 border-2 border-white dark:border-zinc-900 rounded-full p-1.5" title="Pending Verification"></div>
                                @endif
                            </div>
                        </div>

                        <flux:heading size="md">{{ $user->email }}</flux:heading>
                        
                        <div class="mt-2">
                            @php
                                $roleColors = [
                                    'admin' => 'red',
                                    'official' => 'blue',
                                    'resident' => 'green',
                                ];
                            @endphp
                            <flux:badge color="{{ $roleColors[$user->role] ?? 'zinc' }}" size="sm" inset="top bottom">
                                {{ ucfirst($user->role) }}
                            </flux:badge>
                        </div>
                        
                        <div class="mt-6 w-full text-left space-y-3">
                            <div class="text-sm">
                                <span class="block text-zinc-500">Joined</span>
                                <span class="font-medium">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="block text-zinc-500">Last Updated</span>
                                <span class="font-medium">{{ $user->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- RIGHT COLUMN: Resident Details --}}
            <div class="md:col-span-2 space-y-6">
                
                @if($user->resident)
                    @php $res = $user->resident; @endphp
                    
                    {{-- Personal Info Card --}}
                    <flux:card>
                        <div class="mb-4 pb-2 border-b border-zinc-100 dark:border-zinc-700">
                            <flux:heading size="md" icon="user">Personal Information</flux:heading>
                        </div>
                        
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Full Name</dt>
                                <dd class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $res->fname }} {{ $res->mname }} {{ $res->lname }} {{ $res->suffix }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Date of Birth</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ \Carbon\Carbon::parse($res->birthdate)->format('F d, Y') }} 
                                    <span class="text-zinc-400">({{ \Carbon\Carbon::parse($res->birthdate)->age }} yrs)</span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Sex</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100 capitalize">{{ $res->sex ?? 'N/A' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Civil Status</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100 capitalize">{{ $res->civil_status ?? 'N/A' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Phone Number</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100 font-mono">{{ $res->phone_number ?? 'N/A' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Mother's Maiden Name</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $res->mother_maiden_name ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </flux:card>

                    {{-- Address Card --}}
                    <flux:card>
                        <div class="mb-4 pb-2 border-b border-zinc-100 dark:border-zinc-700">
                            <flux:heading size="md" icon="map-pin">Address Details</flux:heading>
                        </div>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Full Address</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $res->purok ? 'Purok ' . $res->purok . ', ' : '' }}
                                    {{ $res->street ? $res->street . ' St, ' : '' }}
                                    {{ $res->barangay }}, {{ $res->municipality }}, {{ $res->province }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Zone / Sitio</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $res->zone ? 'Zone ' . $res->zone : '' }} 
                                    {{ $res->sitio ? '/ Sitio ' . $res->sitio : '' }}
                                    {{ (!$res->zone && !$res->sitio) ? 'N/A' : '' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Household ID</dt>
                                <dd class="mt-1 text-sm font-mono text-zinc-900 dark:text-zinc-100">{{ $res->household_id ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </flux:card>

                    {{-- Sectors Card --}}
                    <flux:card>
                        <div class="mb-4 pb-2 border-b border-zinc-100 dark:border-zinc-700">
                            <flux:heading size="md" icon="tag">Sectors & Status</flux:heading>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @php
                                $sectors = [
                                    'Solo Parent' => $res->solo_parent,
                                    'OFW' => $res->ofw,
                                    'PWD' => $res->is_pwd,
                                    '4Ps' => $res->is_4ps,
                                    'Senior Citizen' => $res->senior_citizen,
                                    'Voter' => $res->voter,
                                    'Unemployed' => $res->unemployed,
                                    'Out of School' => $res->out_of_school_children,
                                ];
                                $hasSector = false;
                            @endphp

                            @foreach($sectors as $label => $isActive)
                                @if($isActive)
                                    @php $hasSector = true; @endphp
                                    <flux:badge color="zinc" size="sm" icon="check">{{ $label }}</flux:badge>
                                @endif
                            @endforeach

                            @if(!$hasSector)
                                <span class="text-sm text-zinc-500 italic">No specific sectors or status indicated.</span>
                            @endif
                        </div>
                    </flux:card>

                @else
                    {{-- Fallback if no resident profile is linked (e.g. pure admin account) --}}
                    <flux:card>
                        <div class="text-center py-6">
                            <flux:icon name="user-slash" class="w-12 h-12 text-zinc-300 mx-auto mb-3" />
                            <flux:heading size="md">No Resident Profile</flux:heading>
                            <p class="text-zinc-500 text-sm mt-1">This user account is not linked to a specific resident profile.</p>
                        </div>
                    </flux:card>
                @endif
                
            </div>
        </div>

        {{-- Danger Zone (Delete) --}}
        <div class="mt-8 pt-8 border-t border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-red-600">Delete Account</h3>
                    <p class="text-xs text-zinc-500 mt-1">Once deleted, this user account cannot be restored. The resident profile will remain.</p>
                </div>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger" size="sm">Delete User</flux:button>
                </form>
            </div>
        </div>

    </div>

</x-layouts::app>