<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            [
                'name' => 'Barangay Clearance',
                'fee' => 50.00,
                'requirements' => "1. Cedula (Community Tax Certificate)\n2. Valid ID",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Certificate of Indigency',
                'fee' => 0.00, // Usually free for indigent residents
                'requirements' => "1. Purpose of Request (e.g., Medical Assistance, Scholarship)\n2. Interview with BHW",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Certificate of Residency',
                'fee' => 50.00,
                'requirements' => "1. Valid ID or Voter's Certification",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Barangay Business Clearance',
                'fee' => 150.00,
                'requirements' => "1. DTI/SEC Registration\n2. Contract of Lease (if renting)\n3. Old Clearance (for renewal)",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Certificate of Good Moral Character',
                'fee' => 50.00,
                'requirements' => "1. Police Clearance (optional)\n2. Valid ID",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Construction/Building Clearance',
                'fee' => 200.00,
                'requirements' => "1. Land Title/Deed of Sale\n2. Building Plan",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('document_types')->insert($types);
    }
}