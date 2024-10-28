<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CandidacyFileRequest extends FormRequest
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
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'position_id' => ['required', 'integer', 'exists:positions,id'],
        'election_id' => ['required', 'integer', 'exists:elections,id'],
        'department_id' => ['nullable', 'integer', 'exists:departments,id'], 
        'party_list_id' => ['nullable', 'integer', 'exists:party_lists,id'],
        ];
    }
}