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
            'middle_name' => ['required', 'string', 'max:255'],
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
        // We match form inputs (first_name) to database columns (fname)
        $resident = Resident::where('fname', $input['first_name'])
                    ->where('lname', $input['last_name'])
                    ->where('mname', $input['middle_name'])
                    ->first();

        // 3. If NO resident is found, stop and show error
        if (! $resident) {
            throw ValidationException::withMessages([
                'first_name' => ['Name not found! You need to register at barangay as resident!'],
            ]);
        }

        // 4. Create the User (Linked to the Resident)
        return User::create([
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'resident_id' => $resident->id, // Link the ID we found
            'role' => 'resident', // Default role
        ]);
    }
}