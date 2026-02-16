<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        if ($this->resident) {
            $fullName = trim("{$this->resident->fname} {$this->resident->lname}");
            return Str::of($fullName)
                ->explode(' ')
                ->take(2)
                ->map(fn ($word) => Str::substr($word, 0, 1))
                ->implode('');
        }
        return '';
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
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

}
