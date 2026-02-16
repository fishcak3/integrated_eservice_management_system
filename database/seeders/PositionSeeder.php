<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'title' => 'Barangay Captain',
                'description' => 'The chief executive of the barangay government (Captain).',
                'max_members' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Barangay Secretary',
                'description' => 'Member of the Sangguniang Barangay (Legislative Council).',
                'max_members' => 1,
                'is_active' => true,
            ],
                        [
                'title' => 'Barangay Treasurer',
                'description' => 'Official custodian of barangay funds and properties.',
                'max_members' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Barangay Kagawad',
                'description' => 'Member of the Sangguniang Barangay (Legislative Council).',
                'max_members' => 7,
                'is_active' => true,
            ],
            [
                'title' => 'SK Chairperson',
                'description' => 'Chairperson of the Sangguniang Kabataan (Youth Council).',
                'max_members' => 1,
                'is_active' => true,
            ],
                        [
                'title' => 'SK Secretary',
                'description' => 'Member of the Sangguniang Kabataan (Youth Council).',
                'max_members' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Barangay Tanod',
                'description' => 'Member of the the Barangay Peacekeeping Action Team (BPAT).',
                'max_members' => 14, 
                'is_active' => true,
            ],
            [
                'title' => 'Barangay Health Worker',
                'description' => 'Frontline health care provider in the community (BHW).',
                'max_members' => 10,
                'is_active' => true,
            ],
            [
                'title' => 'Lupong Tagapamayapa Member',
                'description' => 'Member of the body responsible for amicable settlement of disputes.',
                'max_members' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Day Care Worker',
                'description' => 'Worker responsible for early childhood care and development.',
                'max_members' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($positions as $position) {
            Position::updateOrCreate(
                ['title' => $position['title']], // Check if title exists
                $position // Create or Update with these values
            );
        }
    }
}