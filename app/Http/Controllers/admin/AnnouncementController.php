<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
public function index(Request $request)
    {
        $query = Announcement::whereIn('status', ['draft', 'published']);

        // 1. Search by Title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // 2. Filter by "Date From"
        if ($request->filled('date_from')) {
            // Using 'created_at' to match your "Date Added" label
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // 3. Filter by "Date To"
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Fetch paginated results and append the query string for page links!
        $announcements = $query->latest()->paginate(10)->withQueryString();

        return view('userdashboard.forAdmin.announcement_mgt.index', compact('announcements'));
    }

    public function create()
    {
        return view('userdashboard.forAdmin.announcement_mgt.create');
    }

    public function show(Announcement $announcement)
    {
        return view('userdashboard.forAdmin.announcement_mgt.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        return view('userdashboard.forAdmin.announcement_mgt.edit', compact('announcement'));
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->cover_image) {
            Storage::disk('public')->delete($announcement->cover_image);
        }
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }
    
}