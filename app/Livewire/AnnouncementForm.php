<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Announcement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:published,archived',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:publish_at',
            'cover_image' => 'nullable|image|max:10240', // 10MB Max
        ]);

        // Base Data
        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'publish_at' => $this->publish_at ?: null,
            'expires_at' => $this->expires_at ?: null,
            'user_id' => auth()->id(),
        ];

        // Handle Image Upload
        if ($this->cover_image) {
            // Delete old image if updating
            if ($this->existing_image) {
                Storage::disk('public')->delete($this->existing_image);
            }
            $data['cover_image'] = $this->cover_image->store('announcements', 'public');
        }

        // Create or Update
        if ($this->announcement) {
            $this->announcement->update($data);
            $message = 'Announcement updated successfully.';
        } else {
            // Slug is handled by the Model boot() method, no need to add here
            Announcement::create($data);
            $message = 'Announcement created successfully.';
        }

        session()->flash('success', $message);
        return redirect()->route('announcements.index');
    }

    public function removeImage()
    {
        $this->cover_image = null;
    }
    
    public function removeExistingImage()
    {
        if($this->announcement && $this->existing_image) {
            Storage::disk('public')->delete($this->existing_image);
            $this->announcement->update(['cover_image' => null]);
            $this->existing_image = null;
        }
    }

    public function render()
    {
        return view('livewire.announcement-form'); // Ensure this view exists
    }
}