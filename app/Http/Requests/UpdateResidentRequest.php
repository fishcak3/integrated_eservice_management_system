<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Make sure this is true!
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_disaster_prone_area' => $this->boolean('is_disaster_prone_area'),
            'solo_parent' => $this->boolean('solo_parent'),
            'ofw' => $this->boolean('ofw'),
            'is_pwd' => $this->boolean('is_pwd'),
            'is_4ps_grantee' => $this->boolean('is_4ps_grantee'),
            'out_of_school_children' => $this->boolean('out_of_school_children'),
            'osa' => $this->boolean('osa'),
            'unemployed' => $this->boolean('unemployed'),
            'laborforce' => $this->boolean('laborforce'),
            'isy_isc' => $this->boolean('isy_isc'),
            'senior_citizen' => $this->boolean('senior_citizen'),
            'voter' => $this->boolean('voter'),
        ]);
    }

    public function rules(): array
    {
        return [
            // Household Info
            'household_id' => ['nullable', 'exists:households,id'],
            'household_number' => ['nullable', 'string', 'max:255'],
            'sitio' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'water_source' => ['nullable', 'in:deep_well,water_district,others'],
            'electricity_source' => ['nullable', 'in:grid,generator,none'],
            'is_disaster_prone_area' => ['boolean'],

            // Basic Info
            'relation_to_head' => ['nullable', 'in:head,spouse,child,parent,sibling,other'],
            'fname' => ['required', 'string', 'max:255'],
            'mname' => ['nullable', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,inactive,pending,deceased,transferred'], // Allowed to be changed on update!
            
            // Personal Info
            'phone_number' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_place' => ['nullable', 'string', 'max:255'], // Added birth_place
            'citizenship' => ['nullable', 'string', 'max:255'], // Added citizenship
            'sex' => ['nullable', 'in:male,female,other'],
            'civil_status' => ['nullable', 'in:single,married,widowed'],

            // Sectoral Info
            'solo_parent' => ['boolean'],
            'ofw' => ['boolean'],
            'is_pwd' => ['boolean'],
            'is_4ps_grantee' => ['boolean'],
            'out_of_school_children' => ['boolean'],
            'osa' => ['boolean'],
            'unemployed' => ['boolean'],
            'laborforce' => ['boolean'],
            'isy_isc' => ['boolean'],
            'senior_citizen' => ['boolean'],
            'voter' => ['boolean'],

            // Family Details
            'mother_maiden_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}