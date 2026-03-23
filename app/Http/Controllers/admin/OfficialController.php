<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // ✅ Added Hash for password encryption
use App\Models\Official;
use App\Models\OfficialTerm;
use App\Models\Position;
use App\Models\Resident;
use App\Models\User;

class OfficialController extends Controller
{

    public function index(Request $request)
    {
        $query = Official::query()->with(['resident', 'currentTerm.position']);

        // Check the raw 'terms' relationship for a current status,
        // and apply our dropdown filters to this current term.
        $query->whereHas('terms', function ($q) use ($request) {
            $q->where('status', 'current');

            // 1. Position Filter (Dropdown)
            if ($request->filled('positions')) {
                $q->whereHas('position', function ($posQuery) use ($request) {
                    $posQuery->whereIn('title', $request->input('positions'));
                });
            }

            // 2. Election Year Filter (Dropdown)
            if ($request->filled('election_years')) {
                $q->whereIn('election_year', $request->input('election_years'));
            }
        });

        // 3. Main Text Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            
            $query->where(function ($q) use ($search) {
                $q->whereHas('resident', fn($q2) => 
                    $q2->where('fname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%")
                )
                ->orWhereHas('terms', fn($q2) => 
                    $q2->where('status', 'current')
                       ->whereHas('position', fn($q3) => $q3->where('title', 'like', "%{$search}%"))
                );
            });
        }

        $officials = $query->latest()->paginate(10);
        
        // IMPORTANT: This keeps your filters active when clicking "Next Page"
        $officials->withQueryString(); 

        return view('userdashboard.forAdmin.official_mgt.index', compact('officials'));
    }

    public function create()
    {
        $residents = Resident::orderBy('lname')->get();
        $positions = Position::where('is_active', true)->get();

        return view('userdashboard.forAdmin.official_mgt.create', compact('residents', 'positions'));
    }

    public function store(Request $request)
    {
        // 1. Base Validation
        $validated = $request->validate([
            'resident_id'   => 'required|exists:residents,id',
            'position_id'   => 'required|exists:positions,id',
            'term_start'    => 'required|date',
            'term_end'      => 'nullable|date|after_or_equal:term_start',
            'election_year' => 'required|string|max:20', 
            'status'        => 'required|in:current,completed,resigned,removed',
            'signature_image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048', // 2MB max
        ]);

        $resident = Resident::findOrFail($validated['resident_id']);
        $user = User::where('resident_id', $resident->id)->first();
        $isActive = $validated['status'] === 'current';

        // 2. Account Validation (Only if resident lacks a user account)
        if (!$user) {
            $request->validate([
                'email'    => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8', 'confirmed'],
            ], [
                'email.required' => 'This resident does not have a user account. Please provide an email address.',
                'password.required' => 'Please set a password for their new user account.'
            ]);
        }

        $signaturePath = null;
        if ($request->hasFile('signature_image')) {
            // Stores the file in storage/app/public/signatures
            $signaturePath = $request->file('signature_image')->store('signatures', 'public');
        }

        // --- 3. CHECK IF RESIDENT IS ALREADY ACTIVE ---
        if ($isActive) {
            $isAlreadyActiveOfficial = Official::where('resident_id', $validated['resident_id'])
                ->whereHas('terms', function ($query) {
                    $query->where('status', 'current')
                          ->where('is_active', true);
                })->exists();

            if ($isAlreadyActiveOfficial) {
                return back()
                    ->withErrors(['resident_id' => 'This resident is already serving as an active official. Please edit their record to mark their current term as completed or removed before assigning a new one.'])
                    ->withInput();
            }
        }

        // --- 4. DYNAMIC LIMIT CHECK ---
        $position = Position::findOrFail($validated['position_id']);
        
        if ($isActive) {
            $activeCount = OfficialTerm::where('position_id', $position->id)
                ->where('is_active', true)
                ->where('status', 'current')
                ->count();

            $maxLimit = $position->max_members ?? 1; 

            if ($activeCount >= $maxLimit) {
                return back()
                    ->withErrors(['position_id' => "The limit of {$maxLimit} active {$position->title}(s) has been reached. Please mark an existing term as completed/removed first."])
                    ->withInput();
            }
        }

        // --- 5. CHECK FOR EXACT DUPLICATE TERMS ---
        $duplicateTerm = OfficialTerm::whereHas('official', function($q) use ($resident) {
                $q->where('resident_id', $resident->id);
            })
            ->where('position_id', $validated['position_id'])
            ->whereDate('term_start', $validated['term_start'])
            ->exists();

        if ($duplicateTerm) {
            return back()
                ->withErrors(['term_start' => 'This resident already has a term for this position starting on this exact date. If you need to make changes, please edit their existing record instead.'])
                ->withInput();
        }

        // --- 6. EXECUTE CREATION IN TRANSACTION ---
        DB::transaction(function () use ($validated, $resident, $user, $isActive, $request, $signaturePath) {
            
            // A. Create User if they don't have one
            if (!$user) {
                $user = User::create([
                    'resident_id' => $resident->id, // Linking back to resident based on your DB structure
                    'name'        => trim($resident->fname . ' ' . $resident->lname),
                    'email'       => $request->email,
                    'password'    => Hash::make($request->password),
                    'role'        => $isActive ? 'official' : 'resident', 
                ]);
            } else {
                // If they have an account, just ensure role is updated if they are an active official
                if ($isActive && $user->role === 'resident') {
                    $user->update(['role' => 'official']);
                }
            }

            // B. Create or Find Base Official Record
            // Changed from firstOrCreate to firstOrNew
            $official = Official::firstOrNew(
                ['resident_id' => $validated['resident_id']]
            );

            // Attach the signature if one was uploaded
            if ($signaturePath) {
                $official->e_signature_path = $signaturePath;
            }
            
            // Now save it to the database!
            $official->save();

            // C. Auto-fill term_end if status is not current and no date was provided
            $termEnd = $validated['term_end'];
            if (!$isActive && empty($termEnd)) {
                $termEnd = now()->toDateString();
            }

            // D. Create the Term Record
            $official->terms()->create([
                'position_id'   => $validated['position_id'],
                'term_start'    => $validated['term_start'],
                'term_end'      => $termEnd, 
                'election_year' => $validated['election_year'],
                'status'        => $validated['status'],
                'is_active'     => $isActive, 
            ]);
        });

        return redirect()->route('officials.index')
            ->with('success', 'Official appointed successfully!');
    }

    public function show(Official $official)
    {
        $official->load(['resident', 'terms.position']);

        return view('userdashboard.forAdmin.official_mgt.show', compact('official'));
    }

    public function edit($id)
    {
        $official = Official::findOrFail($id);
        $residents = Resident::orderBy('lname')->get();
        $positions = Position::where('is_active', true)->get();

        return view('userdashboard.forAdmin.official_mgt.edit', compact('official', 'residents', 'positions'));
    }

    public function update(Request $request, Official $official)
    {
        // 1. Validate exactly what is in the form
        $validated = $request->validate([
            'position_id'   => 'required|exists:positions,id',
            'term_start'    => 'required|date',
            'term_end'      => 'nullable|date|after_or_equal:term_start',
            'election_year' => 'required|string|max:20', 
            'status'        => 'required|in:current,completed,resigned,removed',
            'signature_image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048', // Added validation for image
        ]);

        // 2. Auto-determine active status based on the term status
        $isActive = $validated['status'] === 'current';

        // --- 3. DYNAMIC LIMIT CHECK ---
        $position = Position::findOrFail($validated['position_id']);

        if ($isActive) {
            // Count active officials with this position, EXCLUDING the current official being edited
            $activeCount = OfficialTerm::where('position_id', $position->id)
                ->where('is_active', true)
                ->where('status', 'current')
                ->where('official_id', '!=', $official->id) 
                ->count();

            $maxLimit = $position->max_members ?? 1; 

            if ($activeCount >= $maxLimit) {
                return back()
                    ->withErrors([
                        'position_id' => "The limit of {$maxLimit} active {$position->title}(s) has been reached. Please mark an existing term as completed/removed first."
                    ])
                    ->withInput();
            }
        }

        // --- HANDLE FILE UPLOAD BEFORE TRANSACTION ---
        $signaturePath = $official->e_signature_path; // Keep the old one by default
        
        if ($request->hasFile('signature_image')) {
            // If they uploaded a new image, delete the old one from storage first
            if ($official->e_signature_path && Storage::disk('public')->exists($official->e_signature_path)) {
                Storage::disk('public')->delete($official->e_signature_path);
            }
            
            // Store the new file and update the path variable
            $signaturePath = $request->file('signature_image')->store('signatures', 'public');
        }

        // --- 4. UPDATE RECORDS ---
        DB::transaction(function () use ($validated, $official, $isActive, $signaturePath) {
            
            // Update the Base Official Record with the new signature path
            $official->update(['e_signature_path' => $signaturePath]);

            // Find the term to update
            $targetTerm = $official->terms()->where('status', 'current')->first() 
                       ?? $official->terms()->latest('id')->first();

            // --- Auto-fill term_end if status is not current ---
            $termEnd = $validated['term_end'];
            
            // If they are marked completed/resigned/removed AND no date was manually typed in the form
            if (!$isActive && empty($termEnd)) {
                $termEnd = now()->toDateString(); 
            }

            $termData = [
                'position_id'   => $validated['position_id'],
                'term_start'    => $validated['term_start'],
                'term_end'      => $termEnd,      
                'election_year' => $validated['election_year'],
                'status'        => $validated['status'],
                'is_active'     => $isActive, 
            ];

            if ($targetTerm) {
                $targetTerm->update($termData);
            } else {
                $official->terms()->create($termData);
            }

            // --- 5. UPDATE USER ROLE ---
            $user = User::where('resident_id', $official->resident_id)->first();

            // Check if user exists AND is NOT an admin before touching their role
            if ($user && $user->role !== 'admin') {
                
                // If they are being set to an active 'current' official
                if ($isActive) {
                    if ($user->role === 'resident') {
                        $user->update(['role' => 'official']);
                    }
                } else {
                    // If they are being marked completed/resigned/removed,
                    // check if they still hold ANY OTHER active current positions.
                    $hasOtherActiveTerms = OfficialTerm::where('official_id', $official->id)
                        ->where('status', 'current')
                        ->where('is_active', true)
                        ->exists();

                    // If they don't have any other active terms, safely demote them.
                    if (!$hasOtherActiveTerms && $user->role === 'official') {
                        $user->update(['role' => 'resident']);
                    }
                }
            }
        });

        return redirect()->route('officials.index')
                        ->with('success', 'Official record and signature updated successfully.');
    }

    public function destroy(Official $official)
    {
        $official->delete();

        return redirect()->route('officials.index')
            ->with('success', 'Official removed successfully!');
    }

    public function former(Request $request)
    {
        // Querying the terms table directly, filtering out 'current' terms
        $query = \App\Models\OfficialTerm::query()
            ->with(['official.resident', 'position'])
            ->where('status', '!=', 'current');

        // 1. Position Filter
        if ($request->filled('positions')) {
            $query->whereHas('position', function ($q) use ($request) {
                $q->whereIn('title', $request->input('positions'));
            });
        }

        // 2. Election Year Filter
        if ($request->filled('election_years')) {
            $query->whereIn('election_year', $request->input('election_years'));
        }

        // 3. Status Filter (Completed, Resigned, Removed)
        if ($request->filled('statuses')) {
            $query->whereIn('status', $request->input('statuses'));
        }

        // 4. Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                // Search by resident name
                $q->whereHas('official.resident', function ($q2) use ($search) {
                    $q2->where('fname', 'like', "%{$search}%")
                       ->orWhere('lname', 'like', "%{$search}%");
                })
                // Or search by position title
                ->orWhereHas('position', function ($q2) use ($search) {
                    $q2->where('title', 'like', "%{$search}%");
                });
            });
        }

        $officials = $query->latest('term_end')->paginate(10);
        
        // Ensure filters persist on page changes
        $officials->withQueryString();

        return view('userdashboard.forAdmin.official_mgt.former_official.former', compact('officials'));
    }

    public function showFormer($id)
    {
        // Find the specific term and eager load the relationships
        $term = OfficialTerm::with(['official.resident.user', 'position', 'official.terms.position'])->findOrFail($id);

        return view('userdashboard.forAdmin.official_mgt.former_official.showFormer', compact('term'));
    }
}