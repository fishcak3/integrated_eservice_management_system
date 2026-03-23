<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity; 
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'resident_id',
        'email',
        'password',
        'role',
        'profile_photo',
        'supporting_document',
        'verification_status',
        'account_verified_at',   

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

/**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'account_verified_at' => 'datetime', // Add this line!
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's active sessions.
     */
    public function activeSessions()
    {
        return \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $this->id)
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    // <-- SPATIE CONFIG -->
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User account {$eventName}");
    }

    public function isOnline()
    {
        return \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $this->id)
            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->exists();
    }

    /**
     * Get the user's initials
     */

    public function getInitialsAttribute()
    {
        return $this->resident 
            ? strtoupper(substr($this->resident->fname, 0, 1) . substr($this->resident->lname, 0, 1)) 
            : '?';
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    public function residentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'resident_id');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resident 
                ? "{$this->resident->fname} {$this->resident->lname}" 
                : 'System User', // Fallback if no resident is linked
        );
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo 
            ? asset('storage/' . $this->profile_photo) 
            : null;
    }

    public function getDisplayNameAttribute()
    {
        // If user has a linked resident profile, format their name
        if ($this->resident) {
            return ucwords($this->resident->lname . ', ' . $this->resident->fname);
        }

        // Otherwise return the default name
        return $this->name;
    }

    /**
     * Check if the user is currently serving as an official with a specific position title.
     */
    public function isCurrentOfficialPosition(string $positionTitle): bool
    {
        // Check if the user is linked to a resident
        if (!$this->resident) {
            return false;
        }

        // Check if that resident is an official
        $official = \App\Models\Official::where('resident_id', $this->resident_id)->first();
        if (!$official) {
            return false;
        }

        // Get their current active term
        $currentTerm = $official->currentTerm()->with('position')->first();
        if (!$currentTerm || !$currentTerm->position) {
            return false;
        }

        // Check if the position title matches what we are looking for
        return $currentTerm->position->title === $positionTitle;
    }

}
