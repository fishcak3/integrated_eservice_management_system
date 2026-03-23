<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Resident;
use App\Models\BrgySetting;
use Illuminate\Support\Facades\DB;

class ResidentManagement extends Component
{
    use WithPagination; 

    // Keep active tab in the URL (?type=residents or ?type=households)
    #[Url] public $type = 'residents'; 

    // Filters
    #[Url] public $search = '';
    #[Url] public $sitios = [];
    #[Url] public $statuses = [];
    #[Url] public $sectors = [];
    #[Url] public $is_family_head = '';
    #[Url] public $members = ''; // For household member count

    // Reset pagination when any filter is changed
    public function updated($property)
    {
        $filters = ['search', 'sitios', 'statuses', 'sectors', 'is_family_head', 'members', 'type'];
        if (in_array($property, $filters)) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'sitios', 'statuses', 'sectors', 'is_family_head', 'members']);
        $this->resetPage();
    }

    public function render()
    {
        // 1. Fetch Global Sitios (exactly like your controller)
        $global_sitios = BrgySetting::get('sitios');
        if (is_string($global_sitios)) {
            $global_sitios = json_decode($global_sitios, true) ?? [];
        }

        // 2. Determine which data to load based on the $type property
        if ($this->type === 'households') {
            
            // EXACT Household logic from your controller
            $baseQuery = Resident::query()
                ->select('household_id', 'sitio', DB::raw('count(*) as member_count'))
                ->whereNotNull('household_id')
                ->when($this->search, function($q) {
                    $q->where('household_id', 'like', "%{$this->search}%");
                })
                ->when($this->sitios, function($q) {
                    $q->whereIn('sitio', $this->sitios);
                }); // Note: Removed groupBy here, it belongs on the baseQuery below

            // Apply Group By to base query
            $baseQuery->groupBy('household_id', 'sitio');

            $households = DB::query()
                ->fromSub($baseQuery, 'sub')
                ->when($this->members, function($q) {
                    if ($this->members === '1-3') {
                        $q->whereBetween('member_count', [1, 3]);
                    } elseif ($this->members === '4-6') {
                        $q->whereBetween('member_count', [4, 6]);
                    } elseif ($this->members === '7-9') {
                        $q->whereBetween('member_count', [7, 9]);
                    } elseif ($this->members === '10+') {
                        $q->where('member_count', '>=', 10);
                    }
                })
                ->orderBy('household_id', 'asc')
                ->paginate(10);

            return view('livewire.resident-management', [
                'households' => $households,
                'global_sitios' => $global_sitios
            ])->layout('components.layouts.app', ['title' => 'Household Management']);

        } 
        
        // EXACT Resident logic from your controller
        // We pass the public properties as an array so your existing scopeFilter() keeps working perfectly!
        $filters = [
            'search' => $this->search,
            'sitios' => $this->sitios,
            'status' => $this->statuses, // Adjust key if your scope expects 'statuses' plural
            'sectors' => $this->sectors,
            'is_family_head' => $this->is_family_head,
        ];

        $residents = Resident::query()
            ->filter($filters) // <-- Your magic scope!
            ->orderBy('fname', 'asc')
            ->paginate(10);

        return view('livewire.resident-management', [
            'residents' => $residents,
            'global_sitios' => $global_sitios
        ])->layout('components.layouts.app', ['title' => 'Resident Management']);
    }
}