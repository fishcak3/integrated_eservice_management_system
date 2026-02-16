<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Official;
use App\Models\Position;
use App\Models\Resident;
use App\Models\User;

class OfficialController extends Controller
{
    public function index(Request $request)
    {
        $query = Official::query()
            ->with(['resident', 'position']); // <--- Eager load relationships

        // Search logic (optional)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('resident', function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                ->orWhere('lname', 'like', "%{$search}%");
            })->orWhereHas('position', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Get paginated results
        $officials = $query->latest()->paginate(10);

        return view('userdashboard.forAdmin.official_mgt.index', compact('officials'));
    }

    public function create()
    {
        // Get all residents to populate the dropdown
        $residents = Resident::orderBy('lname')->get();
        
        // Get all active positions
        $positions = Position::where('is_active', true)->get();
        
        // (Optional) Get users if you want to link them manually
        $users = User::all();

        return view('userdashboard.forAdmin.official_mgt.create', compact('residents', 'positions', 'users'));
    }

    public function store(Request $request)
    {
        // 1. Validate the form data
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'position_id' => 'required|exists:positions,id',
            'user_id'     => 'nullable|exists:users,id',
            'date_start'  => 'required|date',
            'date_end'    => 'nullable|date|after_or_equal:date_start',
            'is_active'   => 'boolean',
        ]);

        // 2. Create the Official record
        Official::create($validated);

        // 3. Redirect back to the list with a success message
        return redirect()->route('officials.index')
            ->with('success', 'Official appointed successfully!');
    }

    public function show(Official $official)
    {
        return view('userdashboard.forAdmin.official_mgt.show', compact('official'));
    }

    public function edit($id)
    {
        $official = Official::findOrFail($id);
        
        // Fetch data for dropdowns
        $residents = Resident::orderBy('lname')->get();
        $positions = Position::where('is_active', true)->get();
        $users = User::all();

        return view('userdashboard.forAdmin.official_mgt.edit', compact('official', 'residents', 'positions', 'users'));
    }

    // Process the update
    public function update(Request $request, $id)
    {
        $official = Official::findOrFail($id);

        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'position_id' => 'required|exists:positions,id',
            'user_id'     => 'nullable|exists:users,id',
            'date_start'  => 'required|date',
            'date_end'    => 'nullable|date|after_or_equal:date_start',
            'is_active'   => 'boolean',
        ]);

        $official->update($validated);

        return redirect()->route('officials.index')
            ->with('success', 'Official record updated successfully.');
    }

    public function former(Request $request)
    {
        // Fetch officials where is_active is false OR date_end has passed
        $query = Official::query()
            ->with(['resident', 'position'])
            ->where('is_active', false); 

        // Optional: Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('resident', function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                ->orWhere('lname', 'like', "%{$search}%");
            })->orWhereHas('position', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Sort by most recently ended term
        $officials = $query->orderBy('date_end', 'desc')->paginate(10);

        return view('userdashboard.forAdmin.official_mgt.index', compact('officials'));
    }
}
