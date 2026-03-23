<?php

namespace App\Http\Controllers\official;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;

class OfficialAnnouncementController extends Controller
{
    public function index()
    {
        $query = Announcement::whereIn('status', ['draft', 'published']);

        // Pagination
        $announcements = $query->latest()->paginate(10)->withQueryString();

        return view('userdashboard.forOfficial.announcement_mgt.index', compact('announcements'));
    }

    public function show(Announcement $announcement)
    {
        return view('userdashboard.forOfficial.announcement_mgt.show', compact('announcement'));
    }

    public function create()
    {
        return view('userdashboard.forOfficial.announcement_mgt.create');
    }

    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|max:2048', // Max 2MB image

        ]);

        // 2. Handle the file upload if there is one
        $imagePath = null;
        if ($request->hasFile('cover_image')) {
            $imagePath = $request->file('cover_image')->store('announcements', 'public');
        }

        // 3. Create the announcement with forced secure values
        Announcement::create([
            'title' => $validated['title'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']) . '-' . uniqid(),
            'content' => $validated['content'],
            'cover_image' => $imagePath,

            'status' => 'draft', 
            'user_id' => auth()->id(),
        ]);

        // 4. Redirect back to the index with a success message
        return redirect()->route('official.announcements.index')
            ->with('success', 'Draft announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        // Security Check: If the logged-in user is NOT the creator, block them.
        if ($announcement->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to edit this announcement.');
        }

        return view('userdashboard.forOfficial.announcement_mgt.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        // Security check
        if ($announcement->user_id !== auth()->id()) { abort(403); }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old image if you want to save space
            if ($announcement->cover_image) {
                Storage::disk('public')->delete($announcement->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('announcements', 'public');
        }

        $announcement->update($validated);

        return redirect()->route('official.announcements.index')->with('success', 'Updated!');
    }
}