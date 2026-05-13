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
        $id = $this->route('id');

        return [
            'student_code' => [
                'required',
                'string',
                'unique:students,student_code,' . $id
            ],

            'email' => [
                'required',
                'email',
                'unique:students,email,' . $id
            ],

            'phone_number' => ['nullable', 'string'],

            'gender' => ['nullable', 'in:male,female'],

            'national_id' => ['nullable', 'string'],

            'academic_year' => ['nullable', 'in:first,second,third,fourth'],

            'registered_at' => ['nullable', 'date'],

            'face_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:4096'
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'student_code.string' => 'كود الطالب يجب أن يكون نصًا.',
            'student_code.max' => 'كود الطالب لا يجب أن يتجاوز 255 حرف.',
            'student_code.unique' => 'كود الطالب يجب أن يكون فريدًا.',
            'email.email' => 'البريد الإلكتروني يجب أن يكون عنوانًا صحيحًا.',
            'email.unique' => 'البريد الإلكتروني يجب أن يكون فريدًا.',
            'phone_number.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone_number.max' => 'رقم الهاتف لا يجب أن يتجاوز 20 حرف.',
            'gender.in' => 'الجنس يجب أن يكون ذكرًا أو أنثى.',
            'national_id.string' => 'رقم الهوية يجب أن يكون نصًا.',
            'national_id.max' => 'رقم الهوية لا يجب أن يتجاوز 50 حرف.',
            'academic_year.in' => 'السنة الأكاديمية يجب أن تكون من بين: first, second, third, fourth.',
            'registered_at.date' => 'تاريخ التسجيل يجب أن يكون تاريخًا صالحًا.',
            'face_image.image' => 'صورة الوجه يجب أن تكون صورة.',
            'face_image.mimes' => 'صورة الوجه يجب أن تكون من نوع: jpg, jpeg, png.',
            'face_image.max' => 'صورة الوجه لا يجب أن تتجاوز 4 ميجابايت.',
             'face_image' => 'صورة الوجه يجب أن تكون صورة من نوع jpg, jpeg, png ولا تتجاوز 4 ميجابايت.',

        ];
    }
}
