<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Resident; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'], 
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'agree_terms' => ['required', 'accepted'],
        ],[
            'agree_terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy to register.',
        ])->validate();

        $resident = Resident::where('fname', $input['first_name'])
                    ->where('lname', $input['last_name'])
                    ->when(!empty($input['middle_name']), function($q) use ($input) {
                        return $q->where('mname', $input['middle_name']);
                    })
                    ->first();

        if (! $resident) {
            throw ValidationException::withMessages([
                'first_name' => ['Name not found in barangay records! You need to be a resident to register.'],
            ]);
        }

        $existingAccount = User::where('resident_id', $resident->id)->first();

        if ($existingAccount) {
            throw ValidationException::withMessages([
                'email' => ['This resident already has a registered account. Please log in instead.'],
            ]);
        }

        // 1. Create the user and store in a variable instead of returning immediately
        $newUser = User::create([
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'resident_id' => $resident->id, 
            'role' => 'resident',
        ]);

        // --- ADDED NOTIFICATION LOGIC ---
        // 2. Notify Admins about the new registration
        $admins = User::where('role', 'admin')->get();
        $fullName = "{$input['first_name']} {$input['last_name']}";
        
        Notification::send($admins, new SystemAlertNotification(
            'New Account Registered', 
            "Resident {$fullName} has created a portal account.",
            route('users.index') // Assuming your admins view user lists here
        ));
        // --------------------------------

        // 3. Return the created user for Fortify to log them in
        return $newUser;
    }
}