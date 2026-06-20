<?php

namespace App\Http\Requests\CourseRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'course_name' => ['sometimes', 'string', 'max:255'],
            'course_code' => ['sometimes', 'string', 'max:50', 'unique:courses,course_code,' . $id],
            'description' => ['nullable', 'string'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_code.unique' => 'كود المقرر مستخدم بالفعل.',
            'end_date.after'     => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
        ];
    }
}
