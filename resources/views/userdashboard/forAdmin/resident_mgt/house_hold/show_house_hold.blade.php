<x-layouts::app :title="__('Household Details')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('residents.household') }}">Households</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>View</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Household Number {{ $id }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        
        {{-- Header & Back Button --}}
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Household #{{ $id }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">View family members and details</p>
                </div>

                <flux:button href="{{ route('residents.household') }}" icon="arrow-left" variant="subtle">
                    Back to List
                </flux:button>
            </div>
            
            {{-- Optional: Edit Household Action --}}
            {{-- <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Edit Details
            </button> --}}


        {{-- Household Info Card --}}
        @php
            $firstMember = $residents->first();
        @endphp
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- Address Card --}}
            <div class="col-span-2 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Address Information</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-500">Street / Address</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $firstMember->street ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-500">Purok</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $firstMember->purok ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-500">Barangay</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $firstMember->barangay ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-500">Unit Number</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $firstMember->unit_number ?? 'N/A' }}</dd>
                    </div>
                </div>
            </div>

            {{-- Stats Card --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Overview</h3>
                <div class="mt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total Members</span>
                        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            {{ $residents->count() }} People
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                            Active Household
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Members Table --}}
        <div class="flex flex-1 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Family Members</h3>
            </div>
            
            <div class="flex-1 overflow-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="sticky top-0 z-10 bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Role / Relation</th>
                            <th scope="col" class="px-6 py-3">Contact</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 border-t border-gray-200 dark:divide-gray-700 dark:border-gray-700">
                        @forelse ($residents as $resident)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            {{-- Name --}}
                            <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <div class="flex items-center">
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600 dark:bg-gray-600 dark:text-gray-300">
                                        {{ substr($resident->first_name ?? 'U', 0, 1) }}
                                    </div> 
                                    <div class="ml-3">
                                        <div class="text-sm font-semibold">{{ $resident->first_name }} {{ $resident->last_name }}</div>
                                        <div class="text-xs font-normal text-gray-500">ID: #{{ $resident->id }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Role (Assumption: You might have a relationship column, otherwise static) --}}
                            <td class="px-6 py-4">
                                {{ $resident->relationship ?? 'Member' }}
                            </td>

                            {{-- Contact --}}
                            <td class="px-6 py-4">
                                {{ $resident->phone_number ?? 'N/A' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                                    {{ ucfirst($resident->status ?? 'Active') }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('residents.show', $resident->id) }}" class="font-medium text-blue-600 hover:underline dark:text-blue-500">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-500">
                                No members found in this household.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts::app>