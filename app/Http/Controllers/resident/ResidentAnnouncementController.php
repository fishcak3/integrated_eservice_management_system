<?php

namespace App\Http\Controllers\resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;

class ResidentAnnouncementController extends Controller
{
    public function index()
    {
        // Fetch all published, unexpired announcements and paginate them (9 per page for a 3-column grid)
        $announcements = Announcement::where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('publish_at')
                      ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            // Order by publish_at, falling back to created_at if publish_at is null
            ->orderByRaw('COALESCE(publish_at, created_at) DESC')
            ->paginate(9);

        return view('userdashboard.forResident.announcements.index', compact('announcements'));
    }

    public function show(Announcement $announcement)
    {
        // Optional: If you only want residents to see published/active announcements
        // you can double-check the status here. 
        if ($announcement->status !== 'published') {
            abort(404);
        }

        // Load the author relationship so we can display who posted it
        $announcement->load('author');

        return view('userdashboard.forResident.announcements.show', compact('announcement'));
    }
}
