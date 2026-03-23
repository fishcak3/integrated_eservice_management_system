<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentUpdateRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resident_id',
        'current_data',
        'requested_data',
        'supporting_document',
        'request_type',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    // This automatically converts the JSON from the database into a usable PHP array
    protected $casts = [
        'current_data' => 'array',
        'requested_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}