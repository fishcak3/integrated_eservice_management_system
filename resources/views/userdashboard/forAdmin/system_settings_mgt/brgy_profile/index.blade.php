<x-layouts::app title="System Settings">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Barangay Profile</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    <div x-data="{ activeTab: '{{ request()->query('tab', 'general') }}' }" class="flex w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Header Text & Actions --}}
        <div class="mb-2 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div class="space-y-2">
                <flux:heading size="xl" level="1">System Settings</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Manage barangay profile, configurations, and system maintenance.
                </flux:text>
            </div>
        </div>

        {{-- Display Success Message --}}
        @if (session('status'))
            <div class="p-3 bg-green-100 text-green-700 rounded-lg border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        {{-- 1. GENERAL SETTINGS TAB --}}
        <div x-show="activeTab === 'general'">
            
            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- Main Layout Container (Matches Resident Profile) --}}
                <div class="space-y-10">

                    {{-- Section 1: General Details & Logo --}}
                    <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
                        <div class="px-4 sm:px-0">
                            <flux:heading size="lg">Barangay Details</flux:heading>
                            <flux:text variant="subtle" class="mt-1">
                                Basic identifying information, location context, and the official barangay logo.
                            </flux:text>
                        </div>

                        <div class="md:col-span-2">
                            <flux:card class="p-6">
                                <div class="flex flex-col gap-8">
                                    {{-- Logo Upload --}}
                                    <div class="flex items-center gap-6">
                                        <div class="h-24 w-24 rounded-full overflow-hidden border-4 border-gray-100 shadow-sm bg-gray-50 shrink-0">
                                            @if($logo)
                                                <img src="{{ asset('storage/' . $logo) }}" alt="Barangay Logo" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex items-center justify-center h-full w-full text-gray-400">
                                                    <flux:icon name="photo" class="h-10 w-10" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 max-w-sm">
                                            <flux:input type="file" name="logo" size="sm" label="Official Logo" description="Update the official barangay insignia." />
                                        </div>
                                    </div>

                                    <flux:separator variant="subtle" />

                                    {{-- Address Fields --}}
                                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                                        <flux:input name="barangay_name" label="Barangay Name" value="{{ old('barangay_name', $barangay_name) }}" />
                                        <flux:input name="municipality" label="Municipality/City" value="{{ old('municipality', $municipality) }}" />
                                        
                                        <flux:input name="province" label="Province" value="{{ old('province', $province) }}" />
                                        <flux:input name="region" label="Region" value="{{ old('region', $region) }}" />
                                        
                                        <flux:input name="postal_code" label="Postal Code" value="{{ old('postal_code', $postal_code) }}" />
                                        <flux:input name="population" label="Total Population" value="{{ old('population', $population) }}" />

                                        <div class="sm:col-span-2">
                                            <flux:input name="address" label="Barangay Hall Address" value="{{ old('address', $address) }}" />
                                        </div>
                                    </div>
                                </div>
                            </flux:card>
                        </div>
                    </div>
                    
                    <flux:separator variant="subtle" />

                    {{-- Section 2: Sitios Management --}}
                    <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                        <div class="px-4 sm:px-0">
                            <flux:heading size="lg">Sitios / Puroks</flux:heading>
                            <flux:text variant="subtle" class="mt-1">
                                Manage the internal jurisdictions, sitios, or puroks within the barangay.
                            </flux:text>
                        </div>

                        <div class="md:col-span-2">
                            <flux:card class="p-6">
                                @php 
                                    $sitioArray = old('sitios', is_string($global_sitios) ? json_decode($global_sitios, true) ?? explode(',', $global_sitios) : ($global_sitios ?? []));
                                    if(empty($sitioArray)) $sitioArray = [''];
                                @endphp

                                <div x-data="{ sitios: {{ json_encode($sitioArray) }} }" class="space-y-4">
                                    <div class="space-y-3">
                                        <template x-for="(sitio, index) in sitios" :key="index">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-1">
                                                    <flux:input x-model="sitios[index]" name="sitios[]" placeholder="e.g., Tagurarit" aria-label="Sitio Name" />
                                                </div>
                                                <flux:button variant="danger" icon="trash" size="sm" @click="sitios.splice(index, 1)" aria-label="Remove sitio" />
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <flux:button variant="subtle" icon="plus" size="sm" @click="sitios.push('')">
                                        Add Another Sitio
                                    </flux:button>
                                </div>
                            </flux:card>
                        </div>
                    </div>

                    <flux:separator variant="subtle" />

                    {{-- Section 3: Contact Information --}}
                    <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                        <div class="px-4 sm:px-0">
                            <flux:heading size="lg">Contact Information</flux:heading>
                            <flux:text variant="subtle" class="mt-1">
                                Official communication channels for the barangay office.
                            </flux:text>
                        </div>

                        <div class="md:col-span-2">
                            <flux:card class="p-6">
                                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                                    <flux:input name="contact_phone" label="Contact Phone" value="{{ old('contact_phone', $contact_phone) }}" icon="phone" />
                                    <flux:input name="office_email" label="Office Email" value="{{ old('office_email', $office_email) }}" icon="envelope" />
                                </div>
                            </flux:card>

                            {{-- Form Actions (Save Button) --}}
                            <div class="mt-6 flex justify-end gap-x-4">
                                <flux:button href="{{ route('settings.index') }}" variant="ghost">Cancel</flux:button>
                                <flux:button type="submit" variant="primary">Save Changes</flux:button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>

    </div>
</x-layouts::app>