<?php

namespace App\Http\Requests\StudentRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'enrollment_number' => 'required|string|unique:students,enrollment_number',
            'course' => 'required|string|max:255',

        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'enrollment_number.required' => 'The enrollment number field is required.',
            'enrollment_number.unique' => 'The enrollment number has already been taken.',
            'course.required' => 'The course field is required.',
        ];
    }
}
