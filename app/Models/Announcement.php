<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'cover_image',
        'status',
        'priority',
        'is_pinned',
        'published_at',
        'user_id',
    ];

    // Ensure dates are Carbon instances
    protected $casts = [
        'published_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    // Automatically generate slug when creating an announcement
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($announcement) {
            if (empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title) . '-' . Str::random(4);
            }
        });
    }

    // Relationship: Who created the announcement?
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope: Helper to only get visible announcements for residents
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }
}