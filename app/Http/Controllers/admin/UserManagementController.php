<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Resident;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserManagementController extends Controller
{

    public function index(Request $request)
    {
        $query = User::with('resident');

        // 1. Search Filter (Matches Email or Resident Name)
        $query->when($request->filled('search'), function ($q) use ($request) {
            $searchTerm = '%' . $request->search . '%';
            
            $q->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('email', 'like', $searchTerm)
                         ->orWhereHas('resident', function ($residentQuery) use ($searchTerm) {
                             $residentQuery->where('fname', 'like', $searchTerm)
                                           ->orWhere('lname', 'like', $searchTerm);
                         });
            });
        });

        // 2. Role Filter (Checks if role matches any selected checkboxes)
        $query->when($request->filled('roles'), function ($q) use ($request) {
            $q->whereIn('role', $request->roles);
        });

        // 3. Verification Status Filter
        $query->when($request->filled('verification_statuses'), function ($q) use ($request) {
            $q->whereIn('verification_status', $request->verification_statuses);
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->date_from);
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->date_to);
        });

        // 4. Get results, paginate, and append query strings so pagination links don't lose the filters!
        $users = $query->latest()->paginate(10)->withQueryString();

        return view('userdashboard.forAdmin.user_mgt.index', compact('users'));
    }

    public function create() {
        return view('userdashboard.forAdmin.user_mgt.create');
    }

    public function store(Request $request)
    {
        // 1. Validate the incoming request
        $request->validate([
            'resident_id' => ['required', 'exists:residents,id'], 
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,official,resident'],
            'profile_photo' => ['nullable', 'image', 'max:5120'], 
        ]);

        // 2. Perform business logic checks BEFORE opening a transaction
        $resident = Resident::findOrFail($request->resident_id);

        if ($resident->user()->exists()) {
            throw ValidationException::withMessages([
                'resident_id' => ['The selected resident already has a registered user account.']
            ]);
        }

        try {
            // 3. Only open the transaction for the actual writing of data
            DB::transaction(function () use ($request, $resident) {
                
                $photoPath = null;
                if ($request->hasFile('profile_photo')) {
                    $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
                }

                User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                    'resident_id' => $resident->id,
                    'profile_photo' => $photoPath,
                    'email_verified_at' => now(), 
                    'verification_status' => 'verified',
                    'account_verified_at' => now(),
                ]);
            });

            return redirect()->route('users.index')->with('success', 'User account created and linked successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    public function show(User $user) {
        return view('userdashboard.forAdmin.user_mgt.show', compact('user'));
    }

    public function edit(User $user) {
        
        return view('userdashboard.forAdmin.user_mgt.edit', compact('user'));
    }

    public function update(Request $request, string $id)
    {

        $user = User::findOrFail($id);

        // 1. Validate the fields
        $request->validate([
            // Account Fields
            'email' => [
                'required', 'email', 'max:255', 
                Rule::unique('users')->ignore($user->id)
            ],
            'role' => ['required', 'in:admin,official,resident'],
            
            // Password is "nullable" so they can leave it blank to keep current
            'password' => ['nullable', 'confirmed', Password::defaults()],
            
            // File Uploads (Limit photo to 5MB, document to 10MB)
            'profile_photo' => ['nullable', 'image', 'max:5120'], 
            'supporting_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:30720'],

            // Verification
            'verification_status' => ['required', 'in:pending,verified,rejected'],
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                
                // --- HANDLE PROFILE PHOTO ---
                if ($request->hasFile('profile_photo')) {
                    if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                        Storage::disk('public')->delete($user->profile_photo);
                    }
                    $user->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
                }

                // --- HANDLE SUPPORTING DOCUMENT ---
                if ($request->hasFile('supporting_document')) {
                    // Delete old document if it exists on the local disk
                    if ($user->supporting_document && Storage::disk('local')->exists($user->supporting_document)) {
                        Storage::disk('local')->delete($user->supporting_document);
                    }
                    // Save the new document to the private 'local' disk
                    $user->supporting_document = $request->file('supporting_document')->store('supporting-documents', 'local');
}

                // --- UPDATE USER ACCOUNT ---
                $user->email = $request->email;
                $user->role = $request->role;
                $user->verification_status = $request->verification_status;
                
                // If the status is changed to verified, record the timestamp
                if ($request->verification_status === 'verified' && is_null($user->account_verified_at)) {
                    $user->account_verified_at = now();
                } elseif ($request->verification_status !== 'verified') {
                    $user->account_verified_at = null; // Reset if status changed to pending/rejected
                }
                
                // Only update password if a new one was provided
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                
                $user->save();
            });

            return redirect()->route('users.show')
                ->with('success', 'User account updated successfully.');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    public function destroy(User $user) 
    {
        // Delete the photo from storage if it exists
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->delete();
        
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function verify(User $user)
    {
        // Update the status and set the verified timestamp
        $user->update([
            'verification_status' => 'verified',
            'account_verified_at' => now(),
        ]);

        return back()->with('success', 'User account has been successfully verified.');
    }

    public function reject(User $user)
    {
        // Set to rejected and clear the verified timestamp if it existed
        $user->update([
            'verification_status' => 'rejected',
            'account_verified_at' => null,
        ]);

        return back()->with('error', 'User account verification was rejected.');
    }

    public function search(Request $request)
    {
        $search = trim($request->get('search', ''));
        $isInitialLoad = $request->get('initial') === 'true';

        // ALWAYS eager load the resident to prevent N+1 during the map() phase
        $query = User::with('resident')->orderBy('created_at', 'desc');

        if ($isInitialLoad && empty($search)) {
            $accounts = $query->limit(5)->get();
        } elseif (strlen($search) >= 2) {
            // Search email OR resident first/last name (mirroring your index logic)
            $accounts = $query->where('email', 'like', "%{$search}%")
                ->orWhereHas('resident', function ($q) use ($search) {
                    $q->where('fname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%");
                })
                ->limit(5)
                ->get();
        } else {
            return response()->json([]);
        }

        return response()->json($accounts->map(function ($account) {
            return [
                'id'    => $account->id,
                // Assuming $account->resident exists, gracefully fallback if not
                'name'  => $account->resident ? trim($account->resident->fname . ' ' . $account->resident->lname) : 'Unknown',
                'email' => $account->email,
            ];
        }));
    }

    public function viewDocument(User $user)
    {
        // Check if the user has a document and if it actually exists on the server
        if (!$user->supporting_document || !Storage::disk('local')->exists($user->supporting_document)) {
            abort(404, 'This document has been securely deleted to save space, or it does not exist.');
        }

        // Serve the file directly to the admin's browser securely
        return Storage::disk('local')->response($user->supporting_document);
    }
}
