<?php

namespace App\Http\Requests\StudentRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'student_code' => ['required', 'string', 'max:255', 'unique:students,student_code'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:male,female'],
            'national_id' => ['required', 'string', 'max:50'],
            'face_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:4096'
            ],
            'academic_year' => ['nullable', 'in:first,second,third,fourth'],
        ];
    }
    public function messages(): array
    {
        return [
            'first_name.required' => 'الاسم الأول مطلوب.',
            'first_name.string' => 'الاسم الأول يجب أن يكون نصًا.',
            'first_name.max' => 'الاسم الأول لا يجب أن يتجاوز 255 حرف.',
            'last_name.required' => 'الاسم الأخير مطلوب.',
            'last_name.string' => 'الاسم الأخير يجب أن يكون نصًا.',
            'last_name.max' => 'الاسم الأخير لا يجب أن يتجاوز 255 حرف.',
            'student_code.required' => 'كود الطالب مطلوب.',
            'student_code.string' => 'كود الطالب يجب أن يكون نصًا.',
            'student_code.max' => 'كود الطالب لا يجب أن يتجاوز 255 حرف.',
            'student_code.unique' => 'كود الطالب يجب أن يكون فريدًا.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'البريد الإلكتروني يجب أن يكون عنوانًا صحيحًا.',
            'email.max' => 'البريد الإلكتروني لا يجب أن يتجاوز 255 حرف.',
            'email.unique' => 'البريد الإلكتروني يجب أن يكون فريدًا.',
            'phone_number.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone_number.max' => 'رقم الهاتف لا يجب أن يتجاوز 20 حرف.',
            'gender.required' => 'الجنس مطلوب.',
            'gender.in' => 'الجنس يجب أن يكون ذكرًا أو أنثى.',
            'national_id.required' => 'رقم الهوية مطلوب.',
            'national_id.string' => 'رقم الهوية يجب أن يكون نصًا.',
            'national_id.max' => 'رقم الهوية لا يجب أن يتجاوز 50 حرف.',
            'face_image.image' => 'صورة الوجه يجب أن تكون صورة.',
            'face_image.mimes' => 'صورة الوجه يجب أن تكون من نوع: jpg, jpeg, png.',
            'face_image.max' => 'صورة الوجه لا يجب أن تتجاوز 4 ميجابايت.',
            'academic_year.in' => 'السنة الأكاديمية يجب أن تكون من بين: first, second, third, fourth.',
            'face_image' => 'صورة الوجه يجب أن تكون صورة من نوع jpg, jpeg, png ولا تتجاوز 4 ميجابايت.',
        ];
    }
}
