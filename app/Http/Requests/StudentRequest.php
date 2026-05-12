<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
            'email' => ['required', 'email', 'lowercase', 'unique:students,email'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]+$/'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'national_id' => ['nullable', 'string', 'max:20', 'unique:students,national_id'],
            'academic_year' => ['nullable', 'string', 'in:first,second,third,fourth'],
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
            'phone.max' => 'رقم الهاتف لا يجب أن يتجاوز 20 حرفًا',
            'phone.regex' => 'رقم الهاتف غير صحيح',

        ];
    }
}
