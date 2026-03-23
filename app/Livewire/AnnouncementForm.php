<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Announcement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification;

class AnnouncementForm extends Component
{
    use WithFileUploads;

    public ?Announcement $announcement = null;
    
    // Form Fields
    public $title = '';
    public $content = '';
    public $status = 'published'; // Default to published
    
    // Date Fields (New)
    public $publish_at;
    public $expires_at;
    
    // Image Handling
    public $cover_image; 
    public $existing_image; 

    public function mount(?Announcement $announcement = null)
    {
        if ($announcement && $announcement->exists) {
            $this->announcement = $announcement;
            $this->title = $announcement->title;
            $this->content = $announcement->content;
            $this->status = $announcement->status;
            
            // Format dates for datetime-local input (Y-m-d\TH:i)
            $this->publish_at = $announcement->publish_at?->format('Y-m-d\TH:i');
            $this->expires_at = $announcement->expires_at?->format('Y-m-d\TH:i');
            
            $this->existing_image = $announcement->cover_image;
        }
    }

    public function save()
    {
        // Dynamic rules so validation doesn't fail if publish_at is hidden/empty
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'expires_at' => 'nullable|date',
            'cover_image' => 'nullable|image|max:10240', // 10MB Max
        ];

        if ($this->status !== 'published') {
            $rules['publish_at'] = 'nullable|date';
            $rules['expires_at'] .= '|after:publish_at';
        }

        $validated = $this->validate($rules);

        // Base Data
        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'expires_at' => $this->expires_at ?: null,
        ];

        // BUG FIX: Only set the author if this is a brand NEW announcement
        if (!$this->announcement) {
            $data['user_id'] = auth()->id();
        }

        // LOGIC FOR PUBLISH DATE: 
        if ($this->status === 'published') {
            // Keep the original publish date if editing, otherwise set to now
            $data['publish_at'] = $this->announcement?->publish_at ?? now();
        } else {
            // Use manually entered date, or null
            $data['publish_at'] = $this->publish_at ?: null;
        }

        // Handle Image Upload
        if ($this->cover_image) {
            // Delete old image if updating
            if ($this->existing_image) {
                Storage::disk('public')->delete($this->existing_image);
            }
            $data['cover_image'] = $this->cover_image->store('announcements', 'public');
        }

        // --- NEW LOGIC: Check if we need to notify ---
        // Was it unpublished before this save? (Either it's new, or it wasn't published)
        $wasNotPublished = !$this->announcement || $this->announcement->status !== 'published';
        // Is it being published now?
        $isNowPublished = $data['status'] === 'published';

        // Create or Update (ONLY DO THIS ONCE)
        if ($this->announcement) {
            $this->announcement->update($data);
            $message = 'Announcement updated successfully.';
            $savedAnnouncement = $this->announcement;
        } else {
            $savedAnnouncement = Announcement::create($data);
            $message = 'Announcement created successfully.';
        }

        // --- TRIGGER NOTIFICATION ---
        if ($wasNotPublished && $isNowPublished) {
            
            // SYNTAX FIX: Chunk users to prevent memory exhaustion
            User::where('id', '!=', auth()->id())->chunk(100, function ($users) use ($savedAnnouncement) {
                
                // Group the batch of users by their role. 
                // NOTE: Adjust 'role' if your database column is named differently
                $groupedUsers = $users->groupBy('role'); 

                foreach ($groupedUsers as $role => $roleUsers) {
                    
                    // Dynamically generate the correct URL based on the role
                    $actionUrl = match($role) {
                        'admin'    => route('admin.announcements.show', $savedAnnouncement),
                        'official' => route('official.announcements.show', $savedAnnouncement),
                        // Fallback for residents
                        default    => route('resident.announcements.index'), 
                    };

                    // Send the notification to this specific group with their matching URL
                    Notification::send($roleUsers, new SystemAlertNotification(
                        'New Announcement', 
                        "The Barangay has posted a new announcement: " . $savedAnnouncement->title,
                        $actionUrl
                    ));
                }
            });
        }

        session()->flash('success', $message);
        return redirect()->route('admin.announcements.index');
    }

    public function removeImage()
    {
        $this->cover_image = null;
    }
    
    public function removeExistingImage()
    {
        // SECURITY FIX: Rely on the DB to know what file to delete, not the frontend string
        if($this->announcement && $this->announcement->cover_image) {
            Storage::disk('public')->delete($this->announcement->cover_image);
            $this->announcement->update(['cover_image' => null]);
            $this->existing_image = null;
        }
    }

    public function render()
    {
        return view('livewire.announcement-form'); 
    }
}