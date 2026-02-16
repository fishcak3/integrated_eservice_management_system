<x-layouts::app :title="__('Account Management')">

    <x-slot:header>
        <flux:navbar scrollable>
            <flux:navbar.item 
                href="{{ route('users.index') }}" 
                :current="request()->routeIs('users.index')"
                wire:navigate
                icon="users"
            >
                Accounts
            </flux:navbar.item>

        </flux:navbar>
    </x-slot:header>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">User Management</flux:heading>
                <flux:subheading>Manage account holders and their permissions.</flux:subheading>
            </div>
                <flux:button href="{{ route('users.create') }}" variant="primary" icon="plus">Add User</flux:button>
        </div>

        {{-- The Data Table --}}
        <flux:card class="flex-1 p-0 overflow-hidden">

        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <form method="GET">
                <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search users..." class="max-w-sm" /> 
            </form>
        </div>    

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Role</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Date Added</flux:table.column>
                    <flux:table.column class="text-right">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($users as $user)
                        <flux:table.row>
                            <flux:table.cell class="font-medium">
                                <div class="flex items-center gap-2">
                                    <flux:avatar src="{{ $user->profile_photo_url ?? '' }}" name="{{ $user->name }}" size="xs" />
                                    {{ $user->name }}
                                </div>
                            </flux:table.cell>
                            
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            
                            <flux:table.cell>
                                @php
                                    $colors = [
                                        'admin' => 'indigo',
                                        'official' => 'blue',
                                        'resident' => 'zinc',
                                    ];
                                    $color = $colors[$user->role] ?? 'zinc';
                                @endphp
                                <flux:badge size="sm" :color="$color">{{ ucfirst($user->role) }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($user->is_active ?? true) 
                                    <flux:badge size="sm" color="green" inset="top bottom">Active</flux:badge>
                                @else
                                    <flux:badge size="sm" color="red" inset="top bottom">Inactive</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="text-zinc-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </flux:table.cell>

                            <flux:table.cell class="text-right">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" />

                                    <flux:menu>
                                        <flux:menu.item href="{{ route('users.show', $user->id) }}" icon="eye">
                                            View Details
                                        </flux:menu.item>

                                        <flux:menu.separator />

                                        <flux:menu.item href="{{ route('users.edit', $user->id) }}" icon="pencil-square">
                                            Edit
                                        </flux:menu.item>

                                        <flux:menu.separator />

                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <flux:menu.item type="submit" icon="trash" variant="danger">
                                                Delete
                                            </flux:menu.item>
                                        </form>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-500 py-4">
                                No account holders found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

        <div class="mt-4">
            {{ $users->links() }} 
        </div>
    </div>
</x-layouts::app>