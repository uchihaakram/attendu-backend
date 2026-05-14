<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateInstructorRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s\-\']+$/u'],
            'email' => ['required', 'email', 'lowercase', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]+$/'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'role' => ['instructor'],
            'password_confirmation' => ['required_with:password', 'same:password'],

        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'name.string' => 'الاسم يجب أن يكون نصًا',
            'name.max' => 'الاسم لا يجب أن يتجاوز 255 حرفًا',
            'name.regex' => 'الاسم يجب أن يحتوي على أحرف فقط',
            'email.required' => 'الايميل مطلوب',
            'email.email' => 'البريد الإلكتروني يجب أن يكون عنوان بريد إلكتروني صالح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.string' => 'كلمة المرور يجب أن تكون نصًا',
            'password.min' => 'كلمة المرور لا يجب أن تقل عن 6 أحرف',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'gender.required' => 'الجنس مطلوب',
            'gender.string' => 'الجنس يجب أن يكون نصًا',
            'gender.in' => 'الجنس غير صحيح',
        ];
    }
}
