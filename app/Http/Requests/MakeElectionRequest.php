<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MakeElectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'election_type_id' => ['required', 'integer', 'exists:election_types,id'], // Ensure election type exists
            'department_id' => ['nullable', 'integer', 'exists:departments,id'], // Nullable for general election
            'election_name' => ['required', 'string', 'max:255'], // Set max length for the name
            'campaign_start_date' => ['required', 'date', 'before:campaign_end_date'], // Campaign start must be before end
            'campaign_end_date' => ['required', 'date', 'after:campaign_start_date'], // Campaign end must be after start
            'election_start_date' => [
                'required', 
                'date', 
                'after:campaign_end_date', 
                'before:election_end_date' // Election start must be after campaign end and before election end
            ],
            'election_end_date' => ['required', 'date', 'after:election_start_date'], // Election end must be after start
            'status' => ['required', 'in:upcoming,ongoing,completed'] // Limit status to specific values
        ];
    }    
}
