<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplaintTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            [
                'name' => 'Noise Complaint / Public Disturbance',
                'severity_level' => 'low',
                'description' => 'Excessive noise (videoke, loud parties) during curfew hours or disturbing peace.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Neighborhood Quarrel / Gossip',
                'severity_level' => 'low',
                'description' => 'Verbal disagreements, unjust vexation, or rumor-mongering between neighbors.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Property Boundary Dispute',
                'severity_level' => 'medium',
                'description' => 'Conflicts regarding land boundaries, fences, or encroachments.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Vandalism / Damage to Property',
                'severity_level' => 'medium',
                'description' => 'Intentional damage to private or public property.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Collection of Debt (Small Claims)',
                'severity_level' => 'low',
                'description' => 'Failure to pay borrowed money or goods (estafa within small amounts).',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Animal Control Issue',
                'severity_level' => 'medium',
                'description' => 'Stray dogs, livestock damaging crops, or aggressive pets.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Theft / Robbery',
                'severity_level' => 'high',
                'description' => 'Stealing of personal belongings. Usually referred to police if value is high.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Physical Altercation',
                'severity_level' => 'critical',
                'description' => 'Fights involving physical injury. Immediate action required.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('complaint_types')->insert($types);
    }
}