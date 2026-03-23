<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Resident;

class ResidentTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statuses = [];
    public $sitios = [];
    public $sectors = [];

    public $sortField = 'id';
    public $sortDirection = 'desc';

    protected $updatesQueryString = [
        'search',
        'statuses',
        'sitios',
        'sectors'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function render()
    {
        $query = Resident::query()
            ->with('household');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('fname', 'like', "%{$this->search}%")
                  ->orWhere('lname', 'like', "%{$this->search}%")
                  ->orWhere('phone_number', 'like', "%{$this->search}%");
            });
        }

        if ($this->statuses) {
            $query->whereIn('status', $this->statuses);
        }

        if ($this->sitios) {
            $query->whereHas('household', function ($q) {
                $q->whereIn('sitio', $this->sitios);
            });
        }

        if ($this->sectors) {
            foreach ($this->sectors as $sector) {
                $query->where($sector, true);
            }
        }

        $residents = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.official.residents.resident-table', [
            'residents' => $residents
        ]);
    }
}