<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number', 'complainant_id', 'complaint_type_id',
        'respondent_name', 'incident_date', 'incident_details',
        'location', 'status', 'resolution_notes', 'resolved_at'
    ];

    protected $casts = [
        'incident_date' => 'date',
        'resolved_at' => 'datetime',
    ];

    // Link to the Resident (Complainant)
    public function complainant()
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    // Link to the Category
    public function type()
    {
        return $this->belongsTo(ComplaintType::class, 'complaint_type_id');
    }
}
