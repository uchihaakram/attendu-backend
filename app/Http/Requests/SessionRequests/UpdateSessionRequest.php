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
            'start_time'     => ['sometimes', 'date_format:H:i'],
            'end_time'       => ['sometimes', 'date_format:H:i', 'after:start_time'],
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
            'end_time.after'          => 'وقت النهاية يجب أن يكون بعد البداية.',
            'instructor_ids.*.exists' => 'أحد المحاضرين غير موجود.',
        ];
    }
}
