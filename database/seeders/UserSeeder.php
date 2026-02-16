<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Resident;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. ADMIN ACCOUNT (Main)
        $this->createAccount(
            role: 'admin',
            email: 'admin@gmail.com',
            password: 'password',
            residentData: [
                'fname' => 'Ivan',
                'mname' => 'De chavez',
                'lname' => 'Bugarin',
                'sex' => 'male',
                'civil_status' => 'single',
                'birthdate' => '2002-06-05',
                'phone_number' => '09123456789',
                'street' => 'Tagurarit',
                'laborforce' => true,
                'voter' => true,
            ]
        );

        // 2. OFFICIAL ACCOUNT (Kapitan)
        $this->createAccount(
            role: 'official',
            email: 'kapitan@gmail.com',
            password: 'password',
            residentData: [
                'fname' => 'Juan',
                'mname' => 'Dela',
                'lname' => 'Cruz',
                'sex' => 'male',
                'civil_status' => 'married',
                'birthdate' => '1975-01-15',
                'phone_number' => '09987654321',
                'street' => 'Centro',
                'laborforce' => true,
                'voter' => true,
            ]
        );

        // 3. OFFICIAL ACCOUNT (Secretary)
        $this->createAccount(
            role: 'official',
            email: 'secretary@gmail.com',
            password: 'password',
            residentData: [
                'fname' => 'Maria',
                'mname' => 'Clara',
                'lname' => 'Santos',
                'sex' => 'female',
                'civil_status' => 'single',
                'birthdate' => '1995-03-20',
                'phone_number' => '09112223333',
                'street' => 'Poblacion',
                'laborforce' => true,
                'voter' => true,
            ]
        );

        // 4. RESIDENT (Standard)
        $this->createAccount(
            role: 'resident',
            email: 'resident@gmail.com',
            password: 'password',
            residentData: [
                'fname' => 'Pedro',
                'mname' => 'Penduko',
                'lname' => 'Reyes',
                'sex' => 'male',
                'civil_status' => 'single',
                'birthdate' => '2000-12-12',
                'phone_number' => '09223334444',
                'street' => 'Sitio 1',
                'laborforce' => true,
                'voter' => false,
            ]
        );

        // 5. RESIDENT (Indigent / 4Ps / PWD)
        $this->createAccount(
            role: 'resident',
            email: 'indigent@gmail.com',
            password: 'password',
            residentData: [
                'fname' => 'Nena',
                'mname' => 'Lopez',
                'lname' => 'Garcia',
                'sex' => 'female',
                'civil_status' => 'widowed',
                'birthdate' => '1980-05-05',
                'phone_number' => '09334445555',
                'street' => 'Riverside',
                'is_4ps' => true,
                'is_pwd' => true,
                'solo_parent' => true,
                'voter' => true,
            ]
        );

        // 6. RESIDENT (Senior Citizen)
        $this->createAccount(
            role: 'resident',
            email: 'lolo@gmail.com',
            password: 'password',
            residentData: [
                'fname' => 'Jose',
                'mname' => 'Protacio',
                'lname' => 'Rizal',
                'sex' => 'male',
                'civil_status' => 'married',
                'birthdate' => '1950-06-19',
                'phone_number' => '09445556666',
                'street' => 'Heritage',
                'senior_citizen' => true,
                'voter' => true,
            ]
        );
    }

    /**
     * Helper function to create a Resident and linked User.
     */
    private function createAccount(string $role, string $email, string $password, array $residentData): void
    {
        // Default values for common fields
        $defaults = [
            'suffix' => null,
            'region' => 'Region I',
            'province' => 'Pangasinan',
            'municipality' => 'Malasiqui',
            'barangay' => 'Aliaga',
            'household_id' => 'HH-' . rand(100, 999),
            'solo_parent' => false,
            'ofw' => false,
            'is_pwd' => false,
            'is_4ps' => false,
            'out_of_school_children' => false,
            'osa' => false,
            'unemployed' => false,
            'laborforce' => false,
            'isy_isc' => false,
            'senior_citizen' => false,
            'voter' => false,
            'mother_maiden_name' => 'Sample Mother Name',
        ];

        // Merge defaults
        $finalResidentData = array_merge($defaults, $residentData);

        // Create Resident
        $resident = Resident::create($finalResidentData);

        // Create User linked to Resident
        User::create([
            'resident_id' => $resident->id,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'profile_photo' => null,
            
            // --- UPDATED: Add Verification Fields ---
            'verification_status' => 'verified', // Auto-verify seeded users
            'account_verified_at' => now(),      // Set timestamp to now
            'supporting_document' => null,       // No document needed for seeders
        ]);
    }
}