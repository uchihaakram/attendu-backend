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
            'email' => [
                'sometimes',
                'email',
                'unique:students,email,' . $id,
            ],
            'phone_number' => ['nullable', 'string'],
            'gender'       => ['nullable', 'in:male,female'],
            'national_id'  => ['nullable', 'string'],
            'registered_at' => ['nullable', 'date'],
            'group_id'     => ['nullable', 'exists:groups,id'],

            // نفس الشيء (نخليه بدون تأثير)
            'course_ids'   => ['sometimes', 'array'],
            'course_ids.*' => ['exists:courses,id'],

            'face_image'   => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'         => 'البريد الإلكتروني غير صالح.',
            'email.unique'        => 'البريد الإلكتروني مستخدم بالفعل.',
            'gender.in'           => 'الجنس يجب أن يكون male أو female.',
            'registered_at.date'  => 'تاريخ التسجيل غير صالح.',
            'group_id.exists'     => 'الفرقة الدراسية غير موجودة.',
            'course_ids.array'    => 'المواد يجب أن تكون array.',
            'course_ids.min'      => 'يجب اختيار مادة واحدة على الأقل.',
            'course_ids.*.exists' => 'إحدى المواد المختارة غير موجودة.',
            'face_image.image'    => 'الملف يجب أن يكون صورة.',
            'face_image.mimes'    => 'الصورة يجب أن تكون jpg أو jpeg أو png.',
            'face_image.max'      => 'حجم الصورة يجب ألا يتجاوز 4MB.',
        ];
    }
}
