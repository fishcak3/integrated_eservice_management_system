<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Resident;

class UserManagementController extends Controller
{
    public function searchResidents(Request $request)
    {
        $query = $request->input('q');
        
        $residents = Resident::where('fname', 'like', "%{$query}%")
                        ->orWhere('lname', 'like', "%{$query}%")
                        ->with('user')
                        ->limit(10)
                        ->get()
                        ->map(function ($resident) {
                            $resident->has_account = $resident->user !== null;
                            return $resident;
                        });

        return response()->json($residents);
    }

    public function index()
    {
        // 1. Fetch the paginated users
        $users = User::latest()->paginate(10);

        // 2. Calculate Stats
        // We use simple counts here. You can cache these if the DB gets large.
        $stats = [
            'total'     => User::count(),
            'admins'    => User::where('role', 'admin')->count(),
            'officials' => User::where('role', 'official')->count(),
            'residents' => User::where('role', 'resident')->count(),
        ];

        return view('userdashboard.forAdmin.user_mgt.index', compact('users', 'stats'));
    }

    public function create() {
        return view('userdashboard.forAdmin.user_mgt.create');
    }

    public function store(Request $request)
    {
        // 1. Validate the incoming request
        $request->validate([
            // Account Fields
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => ['required', 'in:admin,official,resident'],
            'profile_photo' => ['nullable', 'image', 'max:5120'], // Max 5MB

            // Resident Core Fields (Required for creating a new profile)
            'fname' => ['required', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
            'sex' => ['nullable', 'in:male,female'],
            'civil_status' => ['nullable', 'in:single,married,widowed,separated'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $residentId = $request->resident_id;
                $resident = null;

                // --- SCENARIO A: LINKING TO EXISTING RESIDENT ---
                if ($residentId) {
                    $resident = \App\Models\Resident::findOrFail($residentId);

                    // CRITICAL CHECK: Does this resident already have a user account?
                    if ($resident->user()->exists()) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'resident_id' => ['The selected resident already has a registered user account.']
                        ]);
                    }

                    // Update the existing resident with the latest form data
                    $resident->update($this->getResidentData($request));
                } 
                
                // --- SCENARIO B: CREATING NEW RESIDENT ---
                else {
                    $resident = Resident::create($this->getResidentData($request));
                }

                // --- HANDLE PROFILE PHOTO UPLOAD ---
                $photoPath = null;
                if ($request->hasFile('profile_photo')) {
                    $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
                }

                // --- CREATE THE USER ACCOUNT ---
                User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                    'resident_id' => $resident->id, // Link to the resident we found or created
                    'profile_photo' => $photoPath,
                    'email_verified_at' => now(), // Auto-verify since Admin created it
                ]);
            });

            return redirect()->route('userdashboard.forAdmin.user_mgt.index')
                ->with('success', 'User account created successfully.');

        } catch (\Exception $e) {
            // Return back with input data so they don't have to re-type everything
            return back()->withInput()->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Helper to map request data to resident columns.
     * This handles the boolean conversions for checkboxes automatically.
     */
    private function getResidentData(Request $request): array
    {
        return [
            'fname' => $request->fname,
            'mname' => $request->mname,
            'lname' => $request->lname,
            'suffix' => $request->suffix,
            'birthdate' => $request->birthdate,
            'sex' => $request->sex,
            'civil_status' => $request->civil_status,
            'phone_number' => $request->phone_number,
            'mother_maiden_name' => $request->mother_maiden_name,
            
            // Address
            'region' => $request->region,
            'province' => $request->province,
            'municipality' => $request->municipality,
            'barangay' => $request->barangay,
            'purok' => $request->purok,
            'street' => $request->street,
            'zone' => $request->zone,
            'sitio' => $request->sitio,
            'household_id' => $request->household_id,

            // Booleans (Checkboxes) 
            // $request->boolean() correctly handles "1", "on", true, or null
            'solo_parent' => $request->boolean('solo_parent'),
            'ofw' => $request->boolean('ofw'),
            'is_pwd' => $request->boolean('is_pwd'),
            'is_4ps' => $request->boolean('is_4ps'),
            'senior_citizen' => $request->boolean('senior_citizen'),
            'voter' => $request->boolean('voter'),
            'unemployed' => $request->boolean('unemployed'),
            'out_of_school_children' => $request->boolean('out_of_school_children'),
        ];
    }

    public function show(User $user) {
        return view('userdashboard.forAdmin.user_mgt.show', compact('user'));
    }

    public function edit(User $user) {
        
        return view('userdashboard.forAdmin.user_mgt.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = \App\Models\User::with('resident')->findOrFail($id);

        // 1. Validate
        $request->validate([
            // Account Fields
            'email' => [
                'required', 'email', 'max:255', 
                // Ignore the current user's ID during unique check
                \Illuminate\Validation\Rule::unique('users')->ignore($user->id)
            ],
            'role' => ['required', 'in:admin,official,resident'],
            'profile_photo' => ['nullable', 'image', 'max:5120'], // Max 5MB
            
            // Password is "nullable" here so they can leave it blank to keep current
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],

            // Resident Fields (We validate these even if just updating)
            'fname' => ['required', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                
                // --- HANDLE PROFILE PHOTO ---
                if ($request->hasFile('profile_photo')) {
                    // Delete old photo if it exists
                    if ($user->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo);
                    }
                    // Store new photo
                    $path = $request->file('profile_photo')->store('profile-photos', 'public');
                    $user->profile_photo = $path;
                }

                // --- UPDATE USER ACCOUNT ---
                $user->email = $request->email;
                $user->role = $request->role;
                
                // Only update password if a new one was provided
                if ($request->filled('password')) {
                    $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
                }
                
                $user->save();

                // --- UPDATE LINKED RESIDENT PROFILE ---
                // We use the same helper function from the 'store' method
                if ($user->resident) {
                    $user->resident->update($this->getResidentData($request));
                }
            });

            return redirect()->route('users.index')
                ->with('success', 'User account updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    public function destroy(User $user) {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
