<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\DocumentType;
use App\Models\RequestAttachment;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DocumentRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tracking_code', 'user_id', 'document_type_id', 
        'purpose', 'status', 'remarks', 'requestor_name', 
        'requestor_phone', 'requestor_address', 'mode_of_request', 
        'resident_id', 'assigned_official_id','control_number',
        'validity_period', 'ordinance_number', 'printed_name',
        'is_e_signed', 'approved_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Document Request {$eventName}");
    }

    protected function requestorDisplayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user 
                ? ($this->user->resident ? "{$this->user->resident->fname} {$this->user->resident->lname}" : $this->user->email)
                : $this->requestor_name . ' (Walk-in)',
        );
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function attachments()
    {
        return $this->hasMany(RequestAttachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function official() 
    {
        return $this->belongsTo(User::class, 'assigned_official_id');
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        // 1. Search Filter
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', "%{$search}%")
                  ->orWhere('requestor_name', 'like', "%{$search}%")
                  ->orWhereHas('user.resident', function ($subQ) use ($search) {
                      $subQ->where('fname', 'like', "%{$search}%")
                           ->orWhere('lname', 'like', "%{$search}%");
                  });
            });
        });

        // 2. Status Filter
        $query->when($filters['statuses'] ?? null, function ($query, $statuses) {
            $query->whereIn('status', $statuses);
        });

        // 3. Document Type Filter
        $query->when($filters['doc_types'] ?? null, function ($query, $docTypes) {
            $query->whereIn('document_type_id', $docTypes);
        });

        // 4. Date Filters (Only triggers if BOTH from and to dates are provided)
        $query->when($filters['date_from'] ?? null, function ($q, $date) {
            $q->whereDate('created_at', '>=', $date);
        });

        $query->when($filters['date_to'] ?? null, function ($q, $date) {
            $q->whereDate('created_at', '<=', $date);
        });
    }
    
}