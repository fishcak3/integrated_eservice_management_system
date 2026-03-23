@props([
    'name' => 'account_id',
    'label' => 'Account',
    'placeholder' => 'Type name to search...',
    'required' => false,
    'initialId' => '',
    'initialName' => '',
])

<div class="relative" x-data="accountSearch(@js($initialId), @js($initialName))" @click.away="closeDropdown()">

    {{-- ✅ FIX 1: Add {{ $attributes }} to the hidden input so it gets disabled --}}
    <input type="hidden" name="{{ $name }}" x-model="accountId" {{ $attributes }}>

    <div class="relative">
        {{-- ✅ FIX 2: Add {{ $attributes }} to the flux input --}}
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

        {{-- Clear Button --}}
        <button 
            type="button"
            x-show="accountId"
            @click="clearSelection()"
            class="absolute inset-y-0 right-0 px-3 flex items-center text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition"
        >
            ✕
        </button>
    </div>

    {{-- Selected Confirmation --}}
    <template x-if="accountId">
        <div class="mt-1 text-xs text-green-600">
            Account selected
        </div>
    </template>

    {{-- Search Results Dropdown --}}
    <div 
        x-show="isOpen"
        class="absolute z-50 w-full mt-1 bg-white border rounded-md shadow-lg border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700 overflow-hidden"
        x-transition
        style="display: none;"
    >
        <ul class="max-h-60 overflow-y-auto py-1">
            {{-- Loading State --}}
            <template x-if="isLoading">
                <li class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                    Searching...
                </li>
            </template>

            {{-- Show Results --}}
            <template x-if="!isLoading && searchResults.length > 0">
                <template x-for="account in searchResults" :key="account.id">
                    <li 
                        @click="fillForm(account)"
                        class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 cursor-pointer transition"
                    >
                        <div class="font-medium text-zinc-900 dark:text-zinc-100" x-text="account.name"></div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            <span x-text="account.email ?? 'No Email'"></span>
                        </div>
                    </li>
                </template>
            </template>

            {{-- No Results --}}
            <template x-if="!isLoading && hasSearched && searchResults.length === 0">
                <li class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                    No account found matching "<span x-text="searchQuery" class="font-semibold"></span>".
                </li>
            </template>
        </ul>
    </div>
</div>

@once
<script>
function accountSearch(initId = '', initName = '') {
    return {
        searchQuery: initName, 
        accountId: initId,     
        searchResults: [],
        isOpen: false,
        hasSearched: false,
        isLoading: false,

        async loadInitial() {
            this.isOpen = true;

            if (this.searchQuery.trim() === '' && !this.accountId) {
                this.hasSearched = false;
                this.isLoading = true;

                try {
                    const response = await fetch("{{ route('users.search') }}?initial=true");
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
            this.accountId = '';
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
                // ✅ FIX 3: Ensure your Controller uses $request->get('search') to match this parameter
                const response = await fetch(
                    "{{ route('users.search') }}?search=" + encodeURIComponent(this.searchQuery)
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

        fillForm(account) {
            this.searchQuery = account.name; 
            this.accountId = account.id;
            this.isOpen = false;
            this.hasSearched = false;
        },

        clearSelection() {
            this.searchQuery = '';
            this.accountId = '';
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