<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification;

class PublishScheduledAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish draft announcements that have reached their scheduled publish date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scheduledAnnouncements = Announcement::where('status', 'draft')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->get();

        $count = $scheduledAnnouncements->count();

        if ($count > 0) {
            // Fetch all users once so we don't query the database over and over in the loop
            $users = User::all();

            foreach ($scheduledAnnouncements as $announcement) {
                $announcement->update(['status' => 'published']);

                // --- TRIGGER NOTIFICATION ---
                Notification::send($users, new SystemAlertNotification(
                    'New Announcement', 
                    "The Barangay has posted a new announcement: " . $announcement->title,
                    route('resident.dashboard') // Adjust to where users view announcements
                ));
            }
            
            $this->info("Successfully published {$count} scheduled announcement(s).");
        } else {
            $this->info("No scheduled announcements found to publish.");
        }
    }
}