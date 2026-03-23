<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivedAnnouncementController extends Controller
{
    public function archived(Request $request)
    {
        // 1. FIX: Validate search parameters to prevent SQL errors (500 Error Fix)
        $request->validate([
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Announcement::where('status', 'archived');

        // Search by Title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by "Date From"
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filter by "Date To"
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Fetch paginated results
        $archivedAnnouncements = $query->latest()->paginate(5)->withQueryString();

        return view('userdashboard.forAdmin.announcement_mgt.archived_mgt.archivedIndex', compact('archivedAnnouncements'));
    }

    public function archivedShow(Announcement $announcement)
    {
        // FIX: Prevent showing actively published announcements here
        abort_if($announcement->status !== 'archived', 404);

        return view('userdashboard.forAdmin.announcement_mgt.archived_mgt.archivedShow', compact('announcement'));
    }

    public function archivedEdit(Announcement $announcement)
    {
        // FIX: Prevent editing actively published announcements here
        abort_if($announcement->status !== 'archived', 404);
        
        return view('userdashboard.forAdmin.announcement_mgt.archived_mgt.archivedEdit', compact('announcement'));
    }

    public function updateStatus(Request $request, Announcement $announcement)
    {
        // 1. Validate form fields
        $validated = $request->validate([
            'status' => 'required|in:published,archived',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:publish_at',
            'cover_image' => 'nullable|image|max:2048', 
        ]);

        // 2. FIX: Safely Handle Image Upload without Data Loss
        if ($request->hasFile('cover_image')) {
            // Delete the old image
            if ($announcement->cover_image) {
                Storage::disk('public')->delete($announcement->cover_image);
            }
            // Save the new image
            $validated['cover_image'] = $request->file('cover_image')->store('announcements', 'public');
        } else {
            // Prevent the existing image from being overwritten with null
            unset($validated['cover_image']); 
        }

        // 3. FIX: Check if we are republishing to set Audit Trails
        $isRepublishing = $validated['status'] === 'published' && $announcement->status === 'archived';
        
        if ($isRepublishing) {
            $validated['republished_by'] = auth()->id();
            $validated['republished_at'] = now();
        }

        // Update the announcement
        $announcement->update($validated);

        // 4. FIX: Notify users if the announcement was republished
        if ($isRepublishing) {
            User::where('id', '!=', auth()->id())->chunk(100, function ($users) use ($announcement) {
                
                $groupedUsers = $users->groupBy('role'); 

                foreach ($groupedUsers as $role => $roleUsers) {
                    $actionUrl = match($role) {
                        'admin'    => route('admin.announcements.show', $announcement),
                        'official' => route('official.announcements.show', $announcement),
                        default    => route('resident.announcements.index'), 
                    };

                    Notification::send($roleUsers, new SystemAlertNotification(
                        'Announcement Republished', 
                        "The Barangay has republished an announcement: " . $announcement->title,
                        $actionUrl
                    ));
                }
            });
        }

        // 5. Redirect based on the new status
        if ($announcement->status === 'published') {
            return redirect()->route('admin.announcements.index')
                             ->with('success', 'Announcement republished successfully!');
        }

        return redirect()->route('admin.announcements.archived')
                         ->with('success', 'Archived announcement updated successfully!');
    }
}