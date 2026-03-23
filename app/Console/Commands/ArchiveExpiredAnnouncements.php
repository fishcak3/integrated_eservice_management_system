<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;

class ArchiveExpiredAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:archive-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive published announcements that have passed their expiration date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredAnnouncements = Announcement::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = $expiredAnnouncements->count();

        if ($count > 0) {
            foreach ($expiredAnnouncements as $announcement) {
                $announcement->update(['status' => 'archived']);
            }
            
            $this->info("Successfully archived {$count} expired announcement(s).");
        } else {
            $this->info("No expired announcements found to archive.");
        }
    }
}