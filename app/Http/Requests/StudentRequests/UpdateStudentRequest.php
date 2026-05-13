<?php

namespace App\Http\Requests\StudentRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:students,email',
            'enrollment_number' => 'sometimes|string|unique:students,enrollment_number',
            'course' => 'sometimes|string|max:255',
        ];
    }
    public function messages(): array
    {
        return [
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'enrollment_number.string' => 'The enrollment number must be a string.',
            'enrollment_number.unique' => 'The enrollment number has already been taken.',
            'course.string' => 'The course must be a string.',
            'course.max' => 'The course may not be greater than 255 characters.',
        ];
    }
}
