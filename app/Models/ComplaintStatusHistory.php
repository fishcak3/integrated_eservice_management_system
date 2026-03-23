<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintStatusHistory extends Model
{
    protected $fillable = [
        'complaint_request_id',
        'old_status',
        'new_status',
        'remarks',
        'changed_by_id',
    ];

    /**
     * Get the complaint this history belongs to.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(ComplaintRequest::class, 'complaint_request_id');
    }

    /**
     * Get the user/admin who changed the status.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }


}