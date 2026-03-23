<?php

namespace App\Http\Controllers\official;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resident;
use App\Models\Household; 
use App\Models\BrgySetting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use Illuminate\Support\Arr; 

class OfficialResidentController extends Controller
{
    public function index(Request $request)
    {
        $residents = Resident::query()
            ->filter($request->all())
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        $global_sitios = BrgySetting::get('sitios');

        if (is_string($global_sitios)) {
            $global_sitios = json_decode($global_sitios, true) ?? [];
        } else {
            $global_sitios = []; 
        }

        return view('userdashboard.forOfficial.resident_mgt.index', compact('residents', 'global_sitios'));
    }

    public function create()
    {
        return view('userdashboard.forOfficial.resident_mgt.create');
    }

    public function store(StoreResidentRequest $request)
    {
        $validated = $request->validated();
        
        // Ensure residents created by officials are ALWAYS set to pending
        $validated['status'] = 'pending';

        // 1. Process the Household First
        $householdId = $this->processHousehold($validated);

        // 2. Separate Resident data from Household data
        $residentData = Arr::except($validated, [
            'household_number', 'sitio', 'address', 'monthly_income', 
            'water_source', 'electricity_source', 'is_disaster_prone_area'
        ]);

        // Assign the correct household ID to the resident
        $residentData['household_id'] = $householdId;

        // 3. Create Resident
        Resident::create($residentData);

        return redirect()->route('official.residents.index')
            ->with('success', 'Resident registered successfully. Status is pending admin approval.');
    }

    public function show(Resident $resident)
    {
        return view('userdashboard.forOfficial.resident_mgt.show', compact('resident'));
    }

    public function edit(Resident $resident)
    {
        return view('userdashboard.forOfficial.resident_mgt.edit', compact('resident'));
    }

    public function update(UpdateResidentRequest $request, Resident $resident)
    {
        // 1. Retrieve validated data from the Form Request
        // (Unchecked boxes are already safely set to false here thanks to prepareForValidation!)
        $validated = $request->validated();

        // 2. Process the Household
        $householdId = $this->processHousehold($validated);

        // 3. Separate Resident data from Household data
        $residentData = Arr::except($validated, [
            'household_number', 'sitio', 'address', 'monthly_income', 
            'water_source', 'electricity_source', 'is_disaster_prone_area'
        ]);

        // Assign the processed household ID to the resident
        $residentData['household_id'] = $householdId;

        // 4. Update the Resident
        $resident->update($residentData);

        return redirect()->route('official.residents.index')
            ->with('success', 'Resident updated successfully.');
    }

    private function processHousehold(array $data)
    {
        // If household_id is provided directly, return it
        if (!empty($data['household_id'])) {
            return $data['household_id'];
        }

        // If household_number is provided, find or create the household
        if (!empty($data['household_number'])) {
            $household = Household::firstOrCreate(
                ['household_number' => $data['household_number']],
                ['sitio' => $data['sitio'] ?? null]
            );
            return $household->id;
        }

        return null;
    }
}
