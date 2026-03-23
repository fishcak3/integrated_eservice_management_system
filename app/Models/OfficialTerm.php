<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OfficialTerm extends Model
{
    use LogsActivity;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'official_id',
        'position_id',
        'term_start',
        'term_end',
        'status',
        'election_year',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'term_start' => 'date',
            'term_end' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Official Term {$eventName}");
    }

    /**
     * Get the official associated with this term.
     */
    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class);
    }

    /**
     * Get the position associated with this term.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}