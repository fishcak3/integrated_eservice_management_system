<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Household;
use App\Models\BrgySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use Illuminate\Support\Arr; 


class ResidentsController extends Controller
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

        return view('userdashboard.forAdmin.resident_mgt.index', compact('residents', 'global_sitios'));
    }

    public function create()
    {
        return view('userdashboard.forAdmin.resident_mgt.create');
    }

    public function store(StoreResidentRequest $request)
    {
        $validated = $request->validated();
        $validated['status'] = 'active';

        // 1. Process the Household First
        $householdId = $this->processHousehold($validated);

        // 2. Separate Resident data from Household data
        $residentData = Arr::except($validated, [
            'household_number', 'sitio'
        ]);

        // Assign the correct household ID to the resident
        $residentData['household_id'] = $householdId;

        // 3. Create Resident
        Resident::create($residentData);

        return redirect()->route('admin.residents.index')
            ->with('success', 'Resident registered successfully.');
    }

    public function show(Resident $resident)
    {
        return view('userdashboard.forAdmin.resident_mgt.show', compact('resident'));
    }

    public function edit(Resident $resident)
    {
        return view('userdashboard.forAdmin.resident_mgt.edit', compact('resident'));
    }

    public function update(UpdateResidentRequest $request, Resident $resident)
    {
        // 1. Retrieve validated data from the Form Request
        // (The UpdateResidentRequest automatically handles the true/false for checkboxes now!)
        $validated = $request->validated();

        // REMOVE THE $booleanFields FOREACH LOOP THAT WAS HERE!

        // 2. Update the Resident
        $resident->update($validated);

        // 3. Optional: Trigger specific logic if marked deceased
        if ($resident->status === 'deceased' && method_exists($this, 'handleDeceasedAccount')) {
            $this->handleDeceasedAccount($resident);
        }

        return redirect()->route('admin.residents.index')
            ->with('success', 'Resident updated successfully.');
    }

    public function destroy(Resident $resident)
    {
        $user = User::where('resident_id', $resident->id)->first();
        if ($user) {
            if ($user->profile_photo) Storage::disk('public')->delete($user->profile_photo);
            if ($user->supporting_document) Storage::disk('public')->delete($user->supporting_document);
            $user->delete();
        }

        $resident->delete();

        return redirect()->route('admin.residents.index')->with('success', 'Resident deleted successfully.');
    }
    private function handleDeceasedAccount(Resident $resident)
    {
        $user = User::where('resident_id', $resident->id)->first();
            
        if ($user) {
            if ($user->profile_photo) Storage::disk('public')->delete($user->profile_photo);
            if ($user->supporting_document) Storage::disk('public')->delete($user->supporting_document);
            $user->delete();
        }
    }

    private function processHousehold(array $data): ?int
    {
        // 1. If an existing household was selected from the dropdown, return its ID
        if (!empty($data['household_id'])) {
            return (int) $data['household_id'];
        }

        // 2. If a new household number was typed in, find it or create a brand new one
        if (!empty($data['household_number'])) {
            $household = Household::firstOrCreate(
                ['household_number' => $data['household_number']], // Search criteria
                [                                                  // Creation data if it doesn't exist
                    'sitio' => $data['sitio'] ?? null,
                    // I removed address, monthly_income, etc. because they aren't in your database!
                ]
            );

            return $household->id;
        }

        // Return null if no household info was provided
        return null; 
    }
}