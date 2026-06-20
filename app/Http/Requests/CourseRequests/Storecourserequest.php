<?php

namespace App\Http\Requests\CourseRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_name' => ['required', 'string', 'max:255'],
            'course_code' => ['nullable', 'string', 'max:50', 'unique:courses,course_code'],
            'description' => ['nullable', 'string'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_name.required' => 'اسم المقرر مطلوب.',
            'course_code.unique'   => 'كود المقرر مستخدم بالفعل.',
            'end_date.after'       => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
        ];
    }
}
