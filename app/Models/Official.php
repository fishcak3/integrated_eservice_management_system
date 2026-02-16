<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    protected $fillable = [
        'resident_id',
        'user_id',
        'position_id',
        'date_start',
        'date_end',
        'is_active',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
