<?php

namespace App\Http\Requests\StudentRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('student');

        return [

            'student_code' => [
                'sometimes',
                'string',
                'unique:students,student_code,' . $id
            ],

            'email' => [
                'sometimes',
                'email',
                'unique:students,email,' . $id
            ],

            'phone_number' => [
                'nullable',
                'string'
            ],

            'gender' => [
                'nullable',
                'in:male,female'
            ],

            'national_id' => [
                'nullable',
                'string'
            ],

            'academic_year' => [
                'nullable',
                'in:first,second,third,fourth'
            ],

            'registered_at' => [
                'nullable',
                'date'
            ],

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

            'student_code.unique' =>
                'كود الطالب مستخدم بالفعل.',

            'email.email' =>
                'البريد الإلكتروني غير صالح.',

            'email.unique' =>
                'البريد الإلكتروني مستخدم بالفعل.',

            'gender.in' =>
                'الجنس يجب أن يكون male أو female.',

            'academic_year.in' =>
                'السنة الدراسية غير صحيحة.',

            'registered_at.date' =>
                'تاريخ التسجيل غير صالح.',

            'face_image.image' =>
                'الملف يجب أن يكون صورة.',

            'face_image.mimes' =>
                'الصورة يجب أن تكون jpg أو jpeg أو png.',

            'face_image.max' =>
                'حجم الصورة يجب ألا يتجاوز 4MB.',
        ];
    }
}
