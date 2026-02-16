<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    // 1. LIST: Still needed to show the table
    public function index(Request $request)
    {
        $query = Announcement::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $announcements = $query->latest()->paginate(10);

        return view('userdashboard.forAdmin.announcement_mgt.index', compact('announcements'));
    }

    // 2. CREATE: Still needed to load the page (which holds the Livewire component)
    public function create()
    {
        return view('userdashboard.forAdmin.announcement_mgt.create');
    }

    // 3. SHOW: Still needed to view details
    public function show(Announcement $announcement)
    {
        return view('userdashboard.forAdmin.announcement_mgt.show', compact('announcement'));
    }

    // 4. EDIT: Still needed to load the page (which holds the Livewire component)
    public function edit(Announcement $announcement)
    {
        return view('userdashboard.forAdmin.announcement_mgt.edit', compact('announcement'));
    }

    // 5. DESTROY: Still needed (unless you moved delete logic to Livewire too)
    // The index page typically uses a standard form for delete, so keep this.
    public function destroy(Announcement $announcement)
    {
        if ($announcement->cover_image) {
            Storage::disk('public')->delete($announcement->cover_image);
        }
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    }
    
    // NOTE: 'store' and 'update' were removed because App\Livewire\AnnouncementForm handles them now.
}