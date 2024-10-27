<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
class StoreUserRequest extends FormRequest
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
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'student_id' => ['required', 'exists:students,id', 'unique:users'], // Ensure the student_id exists in the students table
        'department_id' => ['required', 'exists:departments,id'], // Ensure the department_id exists in the departments table
        'section' => ['required', 'string', 'max:100'], // Adjust max length as needed
        'role_id' => ['required', 'exists:roles,id'], // Ensure the role_id exists in the roles table
        'contact_no' => ['required'], // Adjust digits range as needed
        ];
    }
}
