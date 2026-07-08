<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StudentUpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $studentId = $this->user()->student_id;

        return [
            'email'        => ['nullable', 'email', 'unique:students,email,' . $studentId],
            'phone_number' => ['nullable', 'string'],
            'gender'       => ['nullable', 'in:male,female'],
            'national_id'  => ['nullable', 'string'],
            'password'     => ['nullable', 'string', 'min:6', 'confirmed'],
            'face_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ];
    }
}
