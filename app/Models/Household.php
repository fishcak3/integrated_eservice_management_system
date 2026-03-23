<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\Resident;
use App\Models\Household; 
use Illuminate\Http\Request;

class Household extends Model
{
    use LogsActivity;

    protected $fillable = [
        'household_number', 'sitio',
    ];
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Household {$eventName}");
    }
    
    public function members(): HasMany
    {
        return $this->hasMany(Resident::class, 'household_id');
    }

    public function head(): HasOne
    {

        return $this->hasOne(Resident::class, 'household_id')->where('relation_to_head', 'head');
    }

    public static function getHouseholdsPaginated(array $filters, int $perPage = 10)
    {
        return self::query()
            ->withCount('members') 

            ->when($filters['search'] ?? false, function ($q, $search) {
                $q->where('household_number', 'like', "%{$search}%");
            })

            ->when($filters['sitios'] ?? false, function ($q, $sitios) {
                $q->whereIn('sitio', (array) $sitios);
            })

            // FIXED: Member count filter using has()
            ->when($filters['members'] ?? false, function ($q, $members) {
                $membersArray = (array) $members;
                
                $q->where(function ($subQ) use ($membersArray) {
                    foreach ($membersArray as $range) {
                        $subQ->orWhere(function ($query) use ($range) {
                            if ($range === '1-3') {
                                $query->has('members', '>=', 1)->has('members', '<=', 3);
                            } elseif ($range === '4-6') {
                                $query->has('members', '>=', 4)->has('members', '<=', 6);
                            } elseif ($range === '7-9') {
                                $query->has('members', '>=', 7)->has('members', '<=', 9);
                            } elseif ($range === '10+') {
                                $query->has('members', '>=', 10);
                            }
                        });
                    }
                });
            })
            
            ->orderBy('household_number', 'asc')
            ->paginate($perPage)
            ->withQueryString(); 
    }
}