<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'gender' => [ 'string', 'in:male,female,other'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:instructor,assistant'],
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
            'phone.string' => 'رقم الهاتف يجب أن يكون نصًا',
            'phone.max' => 'رقم الهاتف لا يجب أن يتجاوز 20 حرفًا',
            'phone.regex' => 'رقم الهاتف غير صحيح',
            'gender.in' => 'الجنس غير صحيح',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'password_confirmation.required' => 'تأكيد كلمة المرور مطلوب',
            'role.in' => 'الدور غير صحيح',
        ];
    }
}
