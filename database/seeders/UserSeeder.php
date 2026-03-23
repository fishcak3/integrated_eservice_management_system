<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Household;
use App\Models\Position;
use App\Models\Resident;
use App\Models\User;
use App\Models\Official;
use App\Models\OfficialTerm;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');
        $now = Carbon::now();

        // 1. Create a dummy household
        $household = Household::create([
            'household_number' => 'HH-0001',
            'sitio' => 'Centro',
        ]);

        // 2. Create Positions for the officials
        $captainPosition = Position::create(['title' => 'Barangay Captain', 'max_members' => 1]);
        $kagawadPosition = Position::create(['title' => 'Barangay Kagawad', 'max_members' => 7]);

        // ==========================================
        // 3. SEED ADMIN (1 User)
        // ==========================================
        $adminResident = Resident::create([
            'household_id' => $household->id,
            'relation_to_head' => 'head',
            'fname' => 'System',
            'lname' => 'Admin',
            'sex' => 'male',
            'status' => 'active',
        ]);

        User::create([
            'resident_id' => $adminResident->id,
            'email' => 'admin@gmail.com',
            'password' => $password,
            'role' => 'admin',
            'verification_status' => 'verified',
            'account_verified_at' => $now,
        ]);


        // ==========================================
        // 4. SEED OFFICIALS (2 Users)
        // ==========================================
        $officialsData = [
            ['fname' => 'Juan', 'lname' => 'Dela Cruz', 'email' => 'captain@gmail.com', 'position_id' => $captainPosition->id],
            ['fname' => 'Maria', 'lname' => 'Santos', 'email' => 'kagawad@gmail.com', 'position_id' => $kagawadPosition->id],
        ];

        foreach ($officialsData as $data) {
            // A. Create Resident Profile
            $officialResident = Resident::create([
                'household_id' => $household->id,
                'relation_to_head' => 'other',
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'sex' => 'male',
                'status' => 'active',
            ]);

            // B. Create User Account
            User::create([
                'resident_id' => $officialResident->id,
                'email' => $data['email'],
                'password' => $password,
                'role' => 'official',
                'verification_status' => 'verified',
                'account_verified_at' => $now,
            ]);

            // C. Add to Officials Table
            $officialRecord = Official::create([
                'resident_id' => $officialResident->id,
            ]);

            // D. Assign Official Term
            OfficialTerm::create([
                'official_id' => $officialRecord->id,
                'position_id' => $data['position_id'],
                'term_start' => '2023-11-01', // Example start date
                'status' => 'current',
                'election_year' => '2023-2026',
                'is_active' => true,
            ]);
        }


        // ==========================================
        // 5. SEED REGULAR RESIDENTS (2 Users)
        // ==========================================
        for ($i = 1; $i <= 2; $i++) {
            $resident = Resident::create([
                'household_id' => $household->id,
                'relation_to_head' => 'child',
                'fname' => 'Resident',
                'lname' => 'User ' . $i,
                'sex' => 'female',
                'status' => 'active',
            ]);

            User::create([
                'resident_id' => $resident->id,
                'email' => "resident{$i}@gmail.com",
                'password' => $password,
                'role' => 'resident',
                'verification_status' => 'verified',
                'account_verified_at' => $now,
            ]);
        }
    }
}