<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Announcement;
use App\Models\User;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin user to assign as the author
        // Fallback to factory if no user exists (just in case you run this independently)
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create();
        
        $now = Carbon::now();

        $announcements = [
            [
                'title' => 'Scheduled Water Interruption',
                'content' => '<p>Please be informed that there will be a scheduled water interruption on <strong>Saturday, from 8:00 AM to 5:00 PM</strong> due to maintenance work by the water district. Residents are advised to store enough water for their daily needs.</p>',
                'status' => 'published',
                'publish_at' => $now->copy()->subDays(2), // Published 2 days ago
                'expires_at' => clone $now->addDays(5), // Expires in 5 days
                'user_id' => $admin->id,
                'cover_image' => null,
                'republished_by' => null,
                'republished_at' => null,
            ],
            [
                'title' => 'Barangay General Assembly 2026',
                'content' => '<p>All residents are invited to attend the Annual Barangay General Assembly. We will discuss the financial report, upcoming infrastructure projects, and peace and order updates.</p>',
                'status' => 'published',
                'publish_at' => $now->copy()->subWeeks(1),
                'expires_at' => null, // Doesn't expire
                'user_id' => $admin->id,
                'cover_image' => null,
                // Simulating an announcement that was bumped/republished
                'republished_by' => $admin->id, 
                'republished_at' => $now,
            ],
            [
                'title' => 'Upcoming Summer Basketball League',
                'content' => '<p>Registration for the Inter-Sitio Summer Basketball League will open soon. Please prepare your requirements.</p>',
                'status' => 'draft',
                'publish_at' => null,
                'expires_at' => null,
                'user_id' => $admin->id,
                'cover_image' => null,
                'republished_by' => null,
                'republished_at' => null,
            ],
            [
                'title' => 'Typhoon Relief Distribution Complete',
                'content' => '<p>The relief distribution for the recent typhoon has been successfully concluded. Thank you to all the volunteers.</p>',
                'status' => 'archived',
                'publish_at' => $now->copy()->subMonths(2),
                'expires_at' => $now->copy()->subMonths(1), // Expired a month ago
                'user_id' => $admin->id,
                'cover_image' => null,
                'republished_by' => null,
                'republished_at' => null,
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create([
                'title' => $data['title'],
                // Str::slug() automatically turns "Hello World" into "hello-world"
                'slug' => Str::slug($data['title']), 
                'content' => $data['content'],
                'status' => $data['status'],
                'publish_at' => $data['publish_at'],
                'expires_at' => $data['expires_at'],
                'user_id' => $data['user_id'],
                'cover_image' => $data['cover_image'],
                'republished_by' => $data['republished_by'],
                'republished_at' => $data['republished_at'],
            ]);
        }
    }
}