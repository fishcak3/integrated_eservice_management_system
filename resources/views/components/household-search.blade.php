@props([
    'name' => 'existing_household_id',
    'label' => 'Search Existing Household Number',
    'placeholder' => 'e.g. 104-A',
    'required' => false,
    'initialId' => '',
    'initialName' => '',
])

<div class="relative" x-data="householdSearch(@js($initialId), @js($initialName))" @click.away="closeDropdown()">

    <input type="hidden" name="{{ $name }}" x-model="householdId" {{ $attributes }}>

    <div class="relative">
        <flux:input 
            x-model="searchQuery" 
            @input.debounce.300ms="search()" 
            @focus="loadInitial()" 
            @keydown.enter.prevent="selectFirstResult()" 
            label="{{ $label }}" 
            placeholder="{{ $placeholder }}" 
            :required="$required"
            icon="magnifying-glass"
            {{ $attributes }}
        />

        <button 
            type="button"
            x-show="householdId"
            @click="clearSelection()"
            class="absolute inset-y-0 right-0 px-3 mt-7 flex items-center text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition"
        >
            ✕
        </button>
    </div>

    <template x-if="householdId">
        <div class="mt-1 text-xs text-green-600">
            Household selected
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
                <template x-for="household in searchResults" :key="household.id">
                    <li 
                        @click="fillForm(household)"
                        class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 cursor-pointer transition flex justify-between items-center"
                    >
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                Household #<span x-text="household.household_number"></span>
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                <span x-text="household.sitio ?? 'No Sitio'"></span> • Head: <span x-text="household.head_name"></span>
                            </div>
                        </div>
                    </li>
                </template>
            </template>

            <template x-if="!isLoading && hasSearched && searchResults.length === 0">
                <li class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                    No household found matching "<span x-text="searchQuery" class="font-semibold"></span>".
                </li>
            </template>
        </ul>
    </div>
</div>

@once
<script>
function householdSearch(initId = '', initName = '') {
    return {
        searchQuery: initName, 
        householdId: initId,
        searchResults: [],
        isOpen: false,
        hasSearched: false,
        isLoading: false,

        async loadInitial() {
            this.isOpen = true;

            if (this.searchQuery.trim() === '' && !this.householdId) {
                this.hasSearched = false;
                this.isLoading = true;

                try {
                    const response = await fetch("{{ route('households.search') }}?initial=true");
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
            this.householdId = '';
            this.isOpen = true;

            if (this.searchQuery.trim() === '') {
                this.loadInitial();
                return;
            }

            this.hasSearched = true;
            this.isLoading = true;

            try {
                const response = await fetch(
                    "{{ route('households.search') }}?search=" + encodeURIComponent(this.searchQuery)
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

        fillForm(household) {
            this.searchQuery = household.household_number; // Display house number in input
            this.householdId = household.id;               // Send actual DB ID in the hidden input
            this.isOpen = false;
            this.hasSearched = false;
        },

        clearSelection() {
            this.searchQuery = '';
            this.householdId = '';
            this.searchResults = [];
            this.hasSearched = false;
            this.loadInitial(); 
        },

        closeDropdown() {
            this.isOpen = false;
        }
    }
}
</script>
@endonce