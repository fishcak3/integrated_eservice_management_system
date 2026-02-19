<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Resident; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

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
        // 1. Validate the form inputs
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'], // Changed to nullable just in case
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        // 2. Search for the resident in the database
        $resident = Resident::where('fname', $input['first_name'])
                    ->where('lname', $input['last_name'])
                    // Use 'like' or exact match depending on your strictness. 
                    // Using optional input logic for middle name:
                    ->when(!empty($input['middle_name']), function($q) use ($input) {
                        return $q->where('mname', $input['middle_name']);
                    })
                    ->first();

        // 3. If NO resident is found, stop and show error
        if (! $resident) {
            throw ValidationException::withMessages([
                'first_name' => ['Name not found in barangay records! You need to be a resident to register.'],
            ]);
        }

        // ---------------------------------------------------------
        // 3.5 NEW CHECK: Check if this Resident already has an account
        // ---------------------------------------------------------
        $existingAccount = User::where('resident_id', $resident->id)->first();

        if ($existingAccount) {
            throw ValidationException::withMessages([
                'email' => ['This resident already has a registered account. Please log in instead.'],
            ]);
        }
        // ---------------------------------------------------------

        // 4. Create the User (Linked to the Resident)
        return User::create([
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'resident_id' => $resident->id, 
            'role' => 'resident',
        ]);
    }
}