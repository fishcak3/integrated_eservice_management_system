<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\ComplaintType;
use App\Models\ComplaintRequest;
use App\Models\ComplaintStatusHistory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ComplaintRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Added SoftDeletes to match $table->softDeletes()

    protected $fillable = [
        'case_number', 
        'user_id', 'resident_id', 'complainant_name', 'complainant_phone', 'complainant_address',
        'respondent_name', 'respondent_user_id', 'respondent_resident_id',
        'complaint_type_id', 'mode_of_request', 'incident_at', 'location', 'incident_details',
        'status', 'assigned_official_id', 
        'admin_remarks', 'investigation_notes', 'hearing_date', 'resolution_notes', 'resolution'
    ];

    protected $casts = [
        'incident_at' => 'datetime',
        'hearing_date' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Complaint Case {$eventName}");
    }

    // Link to the User (Complainant account)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function official() 
    {
        return $this->belongsTo(User::class, 'assigned_official_id');
    }

    // Link to the Resident (Complainant profile)
    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    // Link to the Category/Type
    public function type()
    {
        return $this->belongsTo(ComplaintType::class, 'complaint_type_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(ComplaintStatusHistory::class, 'complaint_request_id')->latest();
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        // 1. Search Filter
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhere('respondent_name', 'like', "%{$search}%")
                  ->orWhere('complainant_name', 'like', "%{$search}%"); // Updated from walkin_name
            });
        });

        // 2. Status Filter
        $query->when($filters['complaint_statuses'] ?? null, function ($query, $statuses) {
            $query->whereIn('status', $statuses);
        });

        // 3. Severity Filter
        $query->when($filters['severities'] ?? null, function ($query, $severities) {
            $query->whereHas('type', function ($q) use ($severities) {
                $q->whereIn('severity_level', $severities);
            });
        });

        // 4. Date Filters (Only triggers if BOTH from and to dates are provided)
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereDate('created_at', '>=', $filters['date_from'])
                  ->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}