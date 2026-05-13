<?php

namespace App\Http\Requests\StudentRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentFaceRequest extends FormRequest
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
            'face_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',

        ];
    }
    public function messages(): array
    {
        return [
            'face_image.required' => 'The face image field is required.',
            'face_image.image' => 'The face image must be an image file.',
            'face_image.mimes' => 'The face image must be a file of type: jpeg, png, jpg, gif.',
            'face_image.max' => 'The face image may not be greater than 2048 kilobytes.',
        ];
    }
}
