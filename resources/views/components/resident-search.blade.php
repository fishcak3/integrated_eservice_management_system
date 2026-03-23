@props([
    'name' => 'resident_id',
    'label' => 'Resident',
    'placeholder' => 'Type name to search...',
    'required' => false,
    'initialId' => '',
    'initialName' => '',
])

<div class="relative" x-data="residentSearch(@js($initialId), @js($initialName))" @click.away="closeDropdown()">

    {{-- ✅ FIX: Added {{ $attributes }} --}}
    <input type="hidden" name="{{ $name }}" x-model="residentId" {{ $attributes }}>

    <div class="relative">
        {{-- ✅ FIX: Added {{ $attributes }} --}}
        <flux:input 
            x-model="searchQuery" 
            @input.debounce.300ms="search()" 
            @focus="loadInitial()" 
            @keydown.enter.prevent="selectFirstResult()" 
            label="{{ $label }}" 
            placeholder="{{ $placeholder }}" 
            :required="$required"
            {{ $attributes }}
        />

        <button 
            type="button"
            x-show="residentId"
            @click="clearSelection()"
            class="absolute inset-y-0 right-0 px-3 flex items-center text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition"
        >
            ✕
        </button>
    </div>

    <template x-if="residentId">
        <div class="mt-1 text-xs text-green-600">
            Resident selected
        </div>
    </template>

    <div 
        x-show="isOpen"
        class="absolute z-50 w-full mt-1 bg-white border rounded-md shadow-lg border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700 overflow-hidden"
        x-transition
        style="display: none;"
    >
        <ul class="max-h-60 overflow-y-auto py-1">
            <template x-if="isLoading">
                <li class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                    Searching...
                </li>
            </template>

            <template x-if="!isLoading && searchResults.length > 0">
                <template x-for="resident in searchResults" :key="resident.id">
                    <li 
                        @click="fillForm(resident)"
                        class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 cursor-pointer transition"
                    >
                        <div class="font-medium text-zinc-900 dark:text-zinc-100" x-text="resident.full_name"></div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            <span x-text="resident.sitio ?? 'No Sitio'"></span> • 
                            <span x-text="resident.birthdate ?? 'No Birthdate'"></span>
                        </div>
                    </li>
                </template>
            </template>

            <template x-if="!isLoading && hasSearched && searchResults.length === 0">
                <li class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                    No resident found matching "<span x-text="searchQuery" class="font-semibold"></span>".
                </li>
            </template>
        </ul>
    </div>
</div>

@once
<script>
function residentSearch(initId = '', initName = '') {
    return {
        searchQuery: initName, 
        residentId: initId,
        searchResults: [],
        isOpen: false,
        hasSearched: false,
        isLoading: false,

        async loadInitial() {
            this.isOpen = true;

            if (this.searchQuery.trim() === '' && !this.residentId) {
                this.hasSearched = false;
                this.isLoading = true;

                try {
                    const response = await fetch("{{ route('residents.search') }}?initial=true");
                    this.searchResults = await response.json();
                } catch (error) {
                    console.error('Initial load error:', error);
                    this.searchResults = [];
                } finally {
                    this.isLoading = false;
                }
            }
        },

        async search() {
            this.residentId = '';
            this.isOpen = true;

            if (this.searchQuery.trim() === '') {
                this.loadInitial();
                return;
            }

            if (this.searchQuery.trim().length < 2) {
                this.searchResults = [];
                this.hasSearched = false;
                return;
            }

            this.hasSearched = true;
            this.isLoading = true;

            try {
                // ✅ FIX: Again, ensure your Controller matches the ?search= parameter
                const response = await fetch(
                    "{{ route('residents.search') }}?search=" + encodeURIComponent(this.searchQuery)
                );
                this.searchResults = await response.json();

            } catch (error) {
                console.error('Search error:', error);
                this.searchResults = [];
            } finally {
                this.isLoading = false;
            }
        },

        selectFirstResult() {
            if (this.isOpen && this.searchResults.length > 0) {
                this.fillForm(this.searchResults[0]);
            }
        },

        fillForm(resident) {
            this.searchQuery = resident.full_name;
            this.residentId = resident.id;
            this.isOpen = false;
            this.hasSearched = false;
            this.$dispatch('resident-selected', { 
                hasAccount: resident.user_id !== null && resident.user_id !== undefined 
            });
        },

        clearSelection() {
            this.searchQuery = '';
            this.residentId = '';
            this.searchResults = [];
            this.hasSearched = false;
            this.$dispatch('resident-selected', { hasAccount: true }); 
            
            this.loadInitial(); 
        },

        closeDropdown() {
            this.isOpen = false;
        }
    }
}
</script>
@endonce