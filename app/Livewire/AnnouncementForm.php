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
    public $status = 'draft';
    public $priority = 'normal';
    public $is_pinned = false;
    
    // Image Handling
    public $cover_image; 
    public $existing_image; 

    public function mount($announcement = null)
    {
        if ($announcement) {
            $this->announcement = $announcement;
            $this->title = $announcement->title;
            $this->content = $announcement->content;
            $this->status = $announcement->status;
            $this->priority = $announcement->priority;
            $this->is_pinned = (bool) $announcement->is_pinned;
            $this->existing_image = $announcement->cover_image;
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'priority' => 'required|in:normal,high,emergency',
            'cover_image' => 'nullable|image|max:10240', // 10MB Max
            'is_pinned' => 'boolean',
        ]);

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'priority' => $this->priority,
            'is_pinned' => $this->is_pinned,
            'user_id' => auth()->id(),
        ];

        if ($this->cover_image) {
            if ($this->existing_image) {
                Storage::disk('public')->delete($this->existing_image);
            }
            $data['cover_image'] = $this->cover_image->store('announcements', 'public');
        }

        if ($this->announcement) {
            if ($data['status'] === 'published' && $this->announcement->status !== 'published') {
                $data['published_at'] = now();
            }
            $this->announcement->update($data);
            session()->flash('success', 'Announcement updated successfully.');
        } else {
            $data['slug'] = Str::slug($this->title) . '-' . Str::random(4);
            $data['published_at'] = $data['status'] === 'published' ? now() : null;
            Announcement::create($data);
            session()->flash('success', 'Announcement created successfully.');
        }

        return redirect()->route('announcements.index');
    }

    public function removeImage()
    {
        $this->cover_image = null;
    }
    
    public function removeExistingImage()
    {
        $this->existing_image = null;
        if($this->announcement) {
            $this->announcement->update(['cover_image' => null]);
        }
    }

    public function render()
    {
        return view('livewire.announcement-form');
    }
}