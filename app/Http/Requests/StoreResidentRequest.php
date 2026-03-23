<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Make sure this is true!
    }

    /**
     * This method runs BEFORE the validation rules.
     * It ensures that if a checkbox is unchecked (and therefore missing from the request),
     * Laravel explicitly sets it to 'false' so it properly updates the database.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            // Household Boolean
            'is_disaster_prone_area' => $this->boolean('is_disaster_prone_area'),
            
            // Socio-Economic Booleans
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
            // ==========================================
            // 1. HOUSEHOLD INFORMATION
            // ==========================================
            'household_id' => ['nullable', 'exists:households,id'],
            'household_number' => ['nullable', 'string', 'max:255'],
            'sitio' => ['nullable', 'string', 'max:255'],

            // ==========================================
            // 2. BASIC INFORMATION (Resident)
            // ==========================================
            'relation_to_head' => ['nullable', 'in:head,spouse,child,parent,sibling,other'],
            'fname' => ['required', 'string', 'max:255'],
            'mname' => ['nullable', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            
            // ==========================================
            // 3. PERSONAL INFORMATION
            // ==========================================
            'phone_number' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'citizenship' => ['nullable', 'string', 'max:255'], 
            'sex' => ['nullable', 'in:male,female,other'],
            'civil_status' => ['nullable', 'in:single,married,widowed'],

            // ==========================================
            // 4. SOCIO-ECONOMIC / SECTORAL INFO
            // ==========================================
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

            // ==========================================
            // 5. FAMILY DETAILS
            // ==========================================
            'mother_maiden_name' => ['nullable', 'string', 'max:255'],
        ];
    }
    
    /**
     * Optional: Add custom error messages if you want them to be user-friendly.
     */
    public function messages(): array
    {
        return [
            'fname.required' => 'The first name is required.',
            'lname.required' => 'The last name is required.',
            'birthdate.before_or_equal' => 'The birthdate cannot be in the future.',
        ];
    }
}