<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Household;

class Resident extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $fillable = [
        'fname', 'mname', 'lname', 'suffix', 'phone_number',
        'birthdate', 'sex', 'civil_status', 'household_id', 'relation_to_head', 
        'solo_parent', 'ofw', 'is_pwd', 'is_4ps_grantee', 'out_of_school_children', 
        'osa', 'unemployed', 'laborforce', 'isy_isc','citizenship','birth_place',
        'senior_citizen', 'voter', 'mother_maiden_name', 'status', 
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()          // Logs all attributes defined in $fillable
            ->logOnlyDirty()         // Logs ONLY the fields that were actually changed
            ->dontSubmitEmptyLogs()  // Ignores standard 'save' requests with no changes
            ->setDescriptionForEvent(fn(string $eventName) => "Resident {$eventName}");
    }

    // --- RELATIONSHIPS ---

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id');
    }

    public function official(): HasOne
    {
        return $this->hasOne(Official::class);
    }

    // --- ACCESSORS ---
    public function getAgeAttribute(): ?string
    {
        if (!$this->birthdate) {
            return null;
        }

        $now = \Carbon\Carbon::now();

        // Add (int) to force a whole number!
        $years = (int) $this->birthdate->diffInYears($now);

        if ($years > 0) {
            return $years . ' year' . ($years > 1 ? 's' : '') . ' old';
        }

        $months = (int) $this->birthdate->diffInMonths($now);

        if ($months > 0) {
            return $months . ' month' . ($months > 1 ? 's' : '') . ' old';
        }

        $days = (int) $this->birthdate->diffInDays($now);

        return $days . ' day' . ($days !== 1 ? 's' : '') . ' old';
    }

    public function getFullNameAttribute()
    {
        return collect([
            $this->fname,
            $this->mname ? strtoupper(substr($this->mname, 0, 1)) . '.' : null,
            $this->lname,
            $this->suffix,
        ])->filter()->implode(' ');
    }

    public function getFormattedNameAttribute()
    {
        return collect([
            $this->lname ? ucwords($this->lname) . ',' : null,
            ucwords($this->fname),
            $this->mname ? strtoupper(substr($this->mname, 0, 1)) . '.' : null,
            $this->suffix,
        ])->filter()->implode(' ');
    }

    // --- SCOPES ---

    public function scopeFilter($query, array $filters)
    {
        // 1. Search Filter
        $query->when($filters['search'] ?? false, function ($query, $search) {
            $terms = array_filter(explode(' ', $search)); 
            
            $query->where(function ($q) use ($terms, $search) {
                $q->whereRaw("TRIM(REPLACE(CONCAT(fname, ' ', COALESCE(mname,''), ' ', lname), '  ', ' ')) LIKE ?", ["%{$search}%"]);
                
                foreach ($terms as $term) {
                    $q->orWhere('fname', 'like', "%{$term}%")
                      ->orWhere('mname', 'like', "%{$term}%")
                      ->orWhere('lname', 'like', "%{$term}%");
                }
                
                // NEW: Search the relationship for the Sitio or Household Number!
                $q->orWhereHas('household', function($hhQuery) use ($search) {
                    $hhQuery->where('sitio', 'like', "%{$search}%")
                            ->orWhere('household_number', 'like', "%{$search}%");
                });
            });
        });

        // 2. Statuses Filter
        $query->when($filters['statuses'] ?? false, function ($query, $statuses) {
            $query->whereIn('status', (array) $statuses);
        });

        // 3. Sitios Filter (NEW: Filter via the household relationship)
        $query->when($filters['sitios'] ?? false, function ($query, $sitios) {
            $query->whereHas('household', function($hhQuery) use ($sitios) {
                $hhQuery->whereIn('sitio', (array) $sitios);
            });
        });

        // 4. Family Head Filter (NEW: Uses relation_to_head)
        $query->when(!empty($filters['is_family_head']), function ($query) use ($filters) {
            $query->where('relation_to_head', 'head');
        });

        // 5. Family Members Filter
        $query->when(!empty($filters['members']), function ($query) use ($filters) {
            $membersArray = (array) $filters['members'];
            
            $query->whereHas('household', function ($hhQuery) use ($membersArray) {
                $hhQuery->where(function($subQ) use ($membersArray) {
                    foreach ($membersArray as $range) {
                        if ($range === '1-3') {
                            // Check if the household has between 1 and 3 members
                            $subQ->orHas('members', '>=', 1)->has('members', '<=', 3);
                        } elseif ($range === '4-6') {
                            $subQ->orHas('members', '>=', 4)->has('members', '<=', 6);
                        } elseif ($range === '7-9') {
                            $subQ->orHas('members', '>=', 7)->has('members', '<=', 9);
                        } elseif ($range === '10+') {
                            $subQ->orHas('members', '>=', 10);
                        }
                    }
                });
            });
        });

        // 6. Sectors Filter (UPDATED: is_4ps to is_4ps_grantee)
        $query->when(!empty($filters['sectors']), function ($query) use ($filters) {
            $sectors = (array) $filters['sectors']; 
            $allowedSectors = [
                'solo_parent', 'ofw', 'is_pwd', 'is_4ps_grantee', // Updated
                'senior_citizen', 'voter', 'unemployed',
                'out_of_school_children', 'osa',
                'laborforce', 'isy_isc'
            ];

            $query->where(function ($q) use ($sectors, $allowedSectors) {
                foreach ($sectors as $sector) {
                    if (in_array($sector, $allowedSectors)) {
                        $q->orWhere($sector, true);
                    }
                }
            });
        });
    }
}