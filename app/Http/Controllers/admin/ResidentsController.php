<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Added for more robust queries

class ResidentsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch Stats
        $totalResidents = Resident::count();
        $activeHouseholds = Resident::distinct('household_id')->count('household_id');

        // 2. Fetch Residents with Search
        $residents = Resident::query()
            ->with('user') // Eager load user
            ->when($request->search, function ($query, $search) {
                $query->where(function($subQuery) use ($search) {
                    $subQuery->where('fname', 'like', "%{$search}%")
                             ->orWhere('lname', 'like', "%{$search}%")
                             ->orWhere('household_id', 'like', "%{$search}%")
                             ->orWhere('street', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('userdashboard.forAdmin.resident_mgt.index', compact(
            'residents', 
            'totalResidents', 
            'activeHouseholds', 
        ));
    }

    public function create()
    {
        return view('userdashboard.forAdmin.resident_mgt.create');
    }

    public function store(Request $request)
    {
        // 1. Validate - This returns ONLY the validated fields
        $validated = $request->validate([
            // Basic Info
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:50',
            
            // Personal Info
            'phone_number' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'sex' => 'nullable|in:male,female,other',
            'civil_status' => 'nullable|in:single,married,widowed',
            
            // Address
            'region' => 'nullable|string',
            'province' => 'nullable|string',
            'municipality' => 'nullable|string',
            'barangay' => 'nullable|string',
            'street' => 'nullable|string',
            'zone' => 'nullable|string',
            'purok' => 'nullable|string',
            'sitio' => 'nullable|string',
            
            // Household
            'household_id' => 'nullable|string|max:255',
            'mother_maiden_name' => 'nullable|string|max:255',

            // Allow boolean fields to be passed, but we handle logic below
            'solo_parent' => 'nullable',
            'ofw' => 'nullable',
            'is_pwd' => 'nullable',
            'is_4ps' => 'nullable',
            'out_of_school_children' => 'nullable',
            'osa' => 'nullable',
            'unemployed' => 'nullable',
            'laborforce' => 'nullable',
            'isy_isc' => 'nullable',
            'senior_citizen' => 'nullable',
            'voter' => 'nullable',
        ]);

        // 2. Handle Boolean Checkboxes
        // We merge the checkbox logic into the $validated array
        $booleanFields = [
            'solo_parent', 'ofw', 'is_pwd', 'is_4ps', 
            'out_of_school_children', 'osa', 'unemployed', 
            'laborforce', 'isy_isc', 'senior_citizen', 'voter'
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->has($field);
        }
        
        // 3. Set Defaults
        $validated['status'] = 'active';

        // 4. Create using VALIDATED data only
        Resident::create($validated);

        return redirect()->route('residents.index')
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

    public function update(Request $request, Resident $resident)
    {
        // 1. Validate
        $validatedData = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'household_id' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            // Fix: unique rule syntax was slightly off in your snippet
            'email' => 'nullable|email|max:255|unique:users,email,' . ($resident->user->id ?? 'NULL'), 
        ]);

        // 2. Update Resident
        // Note: Email usually belongs to the User model, not Resident. 
        // If you are updating the User email, do it separately:
        if ($request->filled('email') && $resident->user) {
            $resident->user->update(['email' => $request->email]);
        }
        
        // Remove email from $validatedData if it's not in the residents table
        unset($validatedData['email']); 

        $resident->update($validatedData);

        return redirect()->route('residents.index')->with('success', 'Resident updated successfully.');
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();
        return redirect()->route('residents.index')->with('success', 'Resident deleted successfully.');
    }

    // --- HOUSEHOLD METHODS ---

    public function households(Request $request)
    {
        $totalResidents = Resident::count();
        $activeHouseholds = Resident::distinct('household_id')->whereNotNull('household_id')->count();

        // Optimized Query for Grouping
        $households = Resident::query()
            ->select('household_id', 'street', 'purok', 'barangay', DB::raw('count(*) as member_count'))
            ->whereNotNull('household_id')
            ->when($request->search, function($q, $search){
                $q->where('household_id', 'like', "%{$search}%");
            })
            // Important: All non-aggregated columns in SELECT must be in GROUP BY
            ->groupBy('household_id', 'street', 'purok', 'barangay')
            ->orderBy('household_id', 'asc')
            ->paginate(10)
            ->withQueryString(); // Keep search params when paginating

        return view('userdashboard.forAdmin.resident_mgt.index', compact(
            'households', 
            'totalResidents', 
            'activeHouseholds', 
        ));
    }

    public function showHousehold($id)
    {
        $residents = Resident::where('household_id', $id)->get();
        return view('userdashboard.forAdmin.resident_mgt.house_hold.show_house_hold', compact('residents', 'id'));
    }
}