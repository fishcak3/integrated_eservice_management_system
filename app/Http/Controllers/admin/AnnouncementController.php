<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $announcements = $query->latest()->paginate(5);

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

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    }
    
}