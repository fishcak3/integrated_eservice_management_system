<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\OfficialTerm;
use App\Models\Resident;
use App\Models\Position;

class Official extends Model
{
    protected $fillable = [
        'resident_id',
        'e_signature_path',
    ];

    public function terms()
    {
        return $this->hasMany(OfficialTerm::class);
    }

    public function currentTerm()
    {
        return $this->hasOne(OfficialTerm::class)
            ->where('status', 'current')
            ->latestOfMany('term_start');
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function position()
    {
        return $this->hasOneThrough(Position::class, OfficialTerm::class, 'official_id', 'id', 'id', 'position_id')
            ->where('official_terms.status', 'current');
    }
        
}
