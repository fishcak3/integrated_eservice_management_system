<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ComplaintType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'severity_level', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Complaint Type {$eventName}");
    }

    public function complaints()
    {
        return $this->hasMany(ComplaintRequest::class);
    }
}