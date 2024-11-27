<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust this based on your authentication logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'election_id' => ['required', 'exists:elections,id'], // Ensure election exists
            'votes' => ['required', 'array', 'min:1'], // Must be an array with at least one vote
            'votes.*.position_id' => ['required', 'exists:positions,id'], // Valid position ID
            'votes.*.candidate_id' => ['required', 'exists:candidates,id'], // Valid candidate ID
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'election_id.required' => 'Election ID is required.',
            'election_id.exists' => 'The selected election does not exist.',
            'votes.required' => 'Votes are required.',
            'votes.array' => 'Votes must be an array.',
            'votes.min' => 'You must vote for at least one position.',
            'votes.*.position_id.required' => 'Position ID is required for each vote.',
            'votes.*.position_id.exists' => 'The selected position does not exist.',
            'votes.*.candidate_id.required' => 'Candidate ID is required for each vote.',
            'votes.*.candidate_id.exists' => 'The selected candidate does not exist.',
        ];
    }
}
