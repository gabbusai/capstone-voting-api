<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
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
            'student_id' => ['required', 'string', 'exists:students,id'], // Ensure student_id exists in the students table
            'email' => ['required', 'string', 'email', 'exists:users,email'], // Ensure email exists in the users table
            'password' => ['required', 'string', 'min:6'], // Password must be at least 6 characters long
        ];
    }
}




//what modifications can we make because the register method only will work on the mobile version of the system... the system will also include a intranet only in school system that will be deployed on the school computers... the only thing the two systems will share is the database on RDS AWS.. so we need to make a new way to authenticate users in the intranet side of things.. firstly, they will register using their student number, names, email, contact no only... then the OTP will be sent and then they will use that OTP as a code or a token that they can input in the intranet system as authentication how can we do that 