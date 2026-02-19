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
        'publish_at', 
        'expires_at', 
        'user_id'
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($announcement) {
            if (empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title) . '-' . uniqid(); 
            }
        });
        
        static::updating(function ($announcement) {
            if ($announcement->isDirty('title') && empty($announcement->slug)) {
                 $announcement->slug = Str::slug($announcement->title) . '-' . uniqid();
            }
        });
    }

    // Helper: Is this currently visible to residents?
    public function scopeActive($query)
    {
        return $query->where('status', 'published')
                     ->where('publish_at', '<=', now())
                     ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
    
    public function getImageUrlAttribute()
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    // Relationship: Who created the announcement?
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}