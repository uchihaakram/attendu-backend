<?php

namespace App\Http\Requests\SessionRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id'      => ['sometimes', 'exists:courses,id'],
            'session_type'   => ['sometimes', 'string'],
            'day'            => ['sometimes', 'string'],
            'start_time' => ['sometimes', 'date'],
            'end_time'   => ['sometimes', 'date', 'after:start_time'],
            'location'       => ['sometimes', 'string'],
            'group_id'       => ['sometimes', 'exists:groups,id'],
            'instructor_ids' => ['sometimes', 'array'],
            'instructor_ids.*' => ['exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.exists'        => 'المقرر غير موجود.',
            'group_id.exists'         => 'الفرقة غير موجودة.',
            'start_time.required'    => 'وقت البداية مطلوب.',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بصيغة YYYY-MM-DDTHH:MM:SS.',

            'end_time.required'    => 'وقت النهاية مطلوب.',
            'end_time.date_format' => 'وقت النهاية يجب أن يكون بصيغة YYYY-MM-DDTHH:MM:SS.',
            'end_time.after'       => 'وقت النهاية يجب أن يكون بعد البداية.',
            'instructor_ids.*.exists' => 'أحد المحاضرين غير موجود.',
        ];
    }
}
