<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use App\Models\User;

class Resident extends Model
{
    protected $fillable = [
    'fname', 'mname', 'lname', 'suffix', 'phone_number',
    'birthdate', 'sex', 'civil_status', 'region', 'province',
    'municipality', 'barangay', 'street', 'zone', 'sitio', 'purok', 'household_id',
    'solo_parent', 'ofw', 'is_pwd', 'is_4ps', 'out_of_school_children',
    'osa', 'unemployed', 'laborforce', 'isy_isc',
    'senior_citizen', 'voter', 'mother_maiden_name',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
    public function getAge(): ?int
    {
        if (!$this->birthdate) {
            return null;
        }

        return $this->birthdate->diffInYears(Carbon::now());
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

}
