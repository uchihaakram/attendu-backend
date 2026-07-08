<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StudentRegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'student_code' => ['required', 'string', 'max:255', 'unique:students,student_code'],
            'email'        => ['required', 'email', 'max:255', 'unique:students,email', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'gender'       => ['required', 'in:male,female'],
            'national_id'  => ['required', 'string', 'max:50'],
            'group_id'     => ['required', 'exists:groups,id'],

            'password'     => ['required', 'string', 'min:6', 'confirmed'],

            'face_image'   => [
                'required', 'image', 'mimes:jpg,jpeg,png', 'max:10240',
                'dimensions:min_width=500,min_height=650',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'student_code.unique' => 'كود الطالب مستخدم بالفعل.',
            'email.unique'        => 'البريد الإلكتروني مستخدم بالفعل.',
            'password.confirmed'  => 'تأكيد كلمة المرور غير متطابق.',
            'face_image.required' => 'صورة الوجه مطلوبة.',
        ];
    }
}
