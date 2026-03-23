<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Resident;
use App\Models\BrgySetting;

class ResidentIndex extends Component
{
    use WithPagination;

    // These hold our modern search and sort states
    public $search = '';
    public $sortField = 'fname'; // Default sort by First Name
    public $sortDirection = 'asc';

    // This resets the pagination back to page 1 when someone starts typing in the search bar
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Handles the up/down arrow clicks
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        // Eager load the household to prevent N+1 database queries
        $query = Resident::query()->with('household');

        // 1. Instant Search Logic
        if ($this->search) {
            $query->where(function($q) {
                $q->where('fname', 'like', '%' . $this->search . '%')
                  ->orWhere('lname', 'like', '%' . $this->search . '%')
                  ->orWhere('id', 'like', '%' . $this->search . '%');
            });
        }

        // 2. Click-to-Sort Logic
        if ($this->sortField === 'address') {
            // Special case: Join the households table to sort by Sitio
            $query->join('households', 'residents.household_id', '=', 'households.id')
                  ->orderBy('households.sitio', $this->sortDirection)
                  ->select('residents.*'); 
        } else {
            // Standard sort for id, fname, etc.
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $residents = $query->paginate(10);

        // Keep your original global_sitios logic
        $global_sitios = BrgySetting::get('sitios');
        $global_sitios = is_string($global_sitios) ? (json_decode($global_sitios, true) ?? []) : [];

        return view('livewire.resident-index', [
            'residents' => $residents,
            'global_sitios' => $global_sitios,
        ]);
    }
}