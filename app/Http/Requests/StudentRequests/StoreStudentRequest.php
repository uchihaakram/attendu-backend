<?php

namespace App\Http\Requests\StudentRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'student_code' => ['required', 'string', 'max:255', 'unique:students,student_code'],
            'email'        => ['required', 'email', 'max:255', 'unique:students,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'gender'       => ['required', 'in:male,female'],
            'national_id'  => ['required', 'string', 'max:50'],
            'face_image'   => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:10240',
                'dimensions:min_width=500,min_height=650',
            ],
            'group_id'     => ['required', 'exists:groups,id'],

            // ⚠️ هنسيبه زي ما هو عشان frontend ما يتكسرش
            'course_ids'   => ['sometimes', 'array'],
            'course_ids.*' => ['exists:courses,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'   => 'الاسم الأول مطلوب.',
            'first_name.string'     => 'الاسم الأول يجب أن يكون نصًا.',
            'first_name.max'        => 'الاسم الأول لا يجب أن يتجاوز 255 حرف.',
            'last_name.required'    => 'الاسم الأخير مطلوب.',
            'last_name.string'      => 'الاسم الأخير يجب أن يكون نصًا.',
            'last_name.max'         => 'الاسم الأخير لا يجب أن يتجاوز 255 حرف.',
            'student_code.required' => 'كود الطالب مطلوب.',
            'student_code.string'   => 'كود الطالب يجب أن يكون نصًا.',
            'student_code.max'      => 'كود الطالب لا يجب أن يتجاوز 255 حرف.',
            'student_code.unique'   => 'كود الطالب يجب أن يكون فريدًا.',
            'email.required'        => 'البريد الإلكتروني مطلوب.',
            'email.email'           => 'البريد الإلكتروني يجب أن يكون عنوانًا صحيحًا.',
            'email.max'             => 'البريد الإلكتروني لا يجب أن يتجاوز 255 حرف.',
            'email.unique'          => 'البريد الإلكتروني يجب أن يكون فريدًا.',
            'phone_number.string'   => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone_number.max'      => 'رقم الهاتف لا يجب أن يتجاوز 20 حرف.',
            'gender.required'       => 'الجنس مطلوب.',
            'gender.in'             => 'الجنس يجب أن يكون ذكرًا أو أنثى.',
            'national_id.required'  => 'رقم الهوية مطلوب.',
            'national_id.string'    => 'رقم الهوية يجب أن يكون نصًا.',
            'national_id.max'       => 'رقم الهوية لا يجب أن يتجاوز 50 حرف.',
            'face_image.required'   => 'صورة الوجه مطلوبة.',
            'face_image.image'      => 'صورة الوجه يجب أن تكون صورة.',
            'face_image.mimes'      => 'صورة الوجه يجب أن تكون من نوع: jpg, jpeg, png.',
            'face_image.max'        => 'صورة الوجه لا يجب أن تتجاوز 10 ميجابايت.',
            'face_image.dimensions' => 'صورة الوجه يجب أن تكون بحجم 500x650 بكسل على الأقل.',
            'group_id.required'     => 'الفرقة الدراسية مطلوبة.',
            'group_id.exists'       => 'الفرقة الدراسية غير موجودة.',
            'course_ids.required'   => 'يجب اختيار مادة واحدة على الأقل.',
            'course_ids.array'      => 'المواد يجب أن تكون array.',
            'course_ids.min'        => 'يجب اختيار مادة واحدة على الأقل.',
            'course_ids.*.exists'   => 'إحدى المواد المختارة غير موجودة.',
        ];
    }
}
